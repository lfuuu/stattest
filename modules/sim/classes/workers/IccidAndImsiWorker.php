<?php

namespace app\modules\sim\classes\workers;

use app\exceptions\ModelValidationException;
use app\modules\sim\classes\MttApiMvnoConnector;
use app\modules\sim\models\AccountMvno;
use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
use app\modules\sim\traits\OnionSyncTrait;
use LogicException;
use Yii;

class IccidAndImsiWorker extends AbstractWorker implements WorkerInterface
{
    use OnionSyncTrait;

    /**
     * @var Card|null
     */
    private $_originCard = null;

    /**
     * @var Card|null
     */
    private $_virtualCard = null;

    /**
     * @var Imsi|null
     */
    private $_originImsi = null;

    /**
     * @var Imsi|null
     */
    private $_virtualImsi = null;

    /**
     * @var AccountMvno|null
     */
    private $_originAccountMvno = null;

    /**
     * @var integer|null
     */
    private $_dirtyOriginMsisdn = null;

    /**
     * @var integer|null
     */
    private $_dirtyVirtualMsisdn = null;

    /**
     * IccidAndImsiWorker constructor.
     * @param Imsi $originImsi
     * @param Imsi $virtualImsi
     * @throws \Exception
     */
    public function __construct(Imsi $originImsi, Imsi $virtualImsi)
    {
        list($this->_originImsi, $this->_virtualImsi) = [$originImsi, $virtualImsi];
        // Получаем связанный модели Card's
        if (!$this->_originCard = Card::findOne(['iccid' => $this->_originImsi->iccid])) {
            throw new \Exception(sprintf('Отсутствует сим-карта с iccid: %d', $this->_originImsi->iccid));
        }
        if (!$this->_virtualCard = Card::findOne(['iccid' => $this->_virtualImsi->iccid])) {
            throw new \Exception(sprintf('Отсутствует сим-карта с iccid: %d', $this->_virtualImsi->iccid));
        }
        // Инициализация DirtyMsisdn's
        list($this->_dirtyOriginMsisdn, $this->_dirtyVirtualMsisdn) = [
            $this->_originImsi->msisdn, $this->_virtualImsi->msisdn
        ];
        // Журналирование операции инициализации
        $this->journal = [
            'worker' => (new \ReflectionClass($this))->getShortName(),
            'journal' => [
                'initialize' => [
                    'origin_imsi' => $originImsi->getAttributes(),
                    'virtual_imsi' => $virtualImsi->getAttributes(),
                ],
            ],
        ];
    }

    private function _localSyncOperation()
    {
        // Локальная синхронизацию Postgres
        $this->_localSyncPostgres();
    }

    /**
     * Локальная синхронизацию Postgres
     * Процесс - задача: обменять между двумя моделями Imsi imsi и iccid, и в модели Card обменять статусы складов
     * В случае возникновения исключительной ситуации будет произведен откат изменений
     *
     * @throws ModelValidationException
     */
    private function _localSyncPostgres()
    {
        // Получаем локально аттрибуты Imsi'es моделей и меняем их кроме ключей imsi и iccid
        $originAttr = $this->_originImsi->getAttributes();
        $virtualAttr = $this->_virtualImsi->getAttributes();

        // По задаче , между моделями imsi должен произойти обмен между imsi и iccid
        $this->_originImsi->setAttributes(array_slice($virtualAttr, 2));
        $this->_virtualImsi->setAttributes(array_slice($originAttr, 2));

        // Попытка сохранения моделей Imsi
        if (!$this->_originImsi->save()) {
            throw new ModelValidationException($this->_originImsi);
        }
        if (!$this->_virtualImsi->save()) {
            throw new ModelValidationException($this->_virtualImsi);
        }

        // Получаем модели Card's и меняем между собой статусы складов
        list($this->_originCard->status_id, $this->_virtualCard->status_id) = [$this->_virtualCard->status_id, $this->_originCard->status_id];
        // Попытка сохранения моделей Cards
        if (!$this->_originCard->save()) {
            throw new ModelValidationException($this->_originCard);
        }
        if (!$this->_virtualCard->save()) {
            throw new ModelValidationException($this->_virtualCard);
        }

        /**
         * Запись в response значений, которые поменялись.
         *  Массив будет передан контроллеру и отправлен в браузер, где JavaScript поменяет данные в формах
         */
        $this->response = [
            'iccids' => [
                'origin' => $this->_originImsi->iccid,
                'virtual' => $this->_virtualImsi->iccid
            ],
            'imsies' => [
                'origin' => $this->_originImsi->imsi,
                'virtual' => $this->_virtualImsi->imsi
            ],
        ];
    }

    /**
     * Глобальная синхронизация с MttApi
     * Процесс-задача: Восстановление утраченной SIM-карты
     *
     * @throws \yii\base\Exception
     */
    private function _globalSyncOperation()
    {
        /** @var MttApiMvnoConnector $mttApiMvnoConnector */
        $mttApiMvnoConnector = MttApiMvnoConnector::me();
        // Проверяем свободность Imsi
        if (!$mttApiMvnoConnector->isImsiOpened($this->_virtualImsi->imsi)) {
            throw new LogicException(sprintf('Сим-карта с virtualImsi %s уже привязана', $this->_virtualImsi->imsi));
        }
        // Проверка существования OriginMsisdn в AccountData
        $this->_originAccountMvno = $mttApiMvnoConnector->getAccountData([
            'msisdn' => $this->_dirtyOriginMsisdn
        ]);
        if ($this->_originAccountMvno->isEmpty) {
            throw new LogicException(sprintf('Аккаунт с originMsisdn %s отсутствует', $this->_dirtyOriginMsisdn));
        }
        $this->journal['journal']['global_sync'][] = [
            'message' => 'Успешно получен originAccountMvno',
            'attributes' => $this->_originAccountMvno->getAttributes(),
            'status' => 'success',
        ];
        // В originAccountMvno освобождаем Origin MSISDN и заменяем его свободным virtualMsisdn
        $updateCustomerOrigin = $mttApiMvnoConnector
            ->updateCustomerByImsi($this->_originAccountMvno->customer_name, $this->_virtualImsi->imsi);
        if ($mttApiMvnoConnector->isImsiOpened($this->_virtualImsi->imsi) || !$mttApiMvnoConnector->isImsiOpened()) {
            // Журналирование критической ситуации
            $this->journal['journal']['global_sync'][] = [
                'message' => 'Ошибка обмена imsies с originImsi на virtualImsi.',
                'status' => 'exception',
            ];
            throw new LogicException(json_encode($this->journal));
        }
        $this->journal['journal']['global_sync'][] = [
            'message' => 'Успешная замена imsies между originImsi и virtualImsi.',
            'status' => 'success',
        ];
        Yii::info($this->journal, __METHOD__);
    }
}