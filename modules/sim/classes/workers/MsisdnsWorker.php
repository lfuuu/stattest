<?php

namespace app\modules\sim\classes\workers;

use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\sim\classes\MttApiMvnoConnector;
use app\modules\sim\models\AccountMvno;
use app\modules\sim\models\Imsi;
use app\modules\sim\traits\OnionSyncTrait;
use LogicException;
use Yii;
use yii\base\InvalidParamException;

class MsisdnsWorker extends AbstractWorker implements WorkerInterface
{
    use OnionSyncTrait;

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
     * @var AccountMvno|null
     */
    private $_virtualAccountMvno = null;

    /**
     * @var integer|null
     */
    private $_dirtyOriginMsisdn = null;

    /**
     * @var integer|null
     */
    private $_dirtyVirtualMsisdn = null;

    /**
     * @param Imsi $originImsi
     * @param Imsi $virtualImsi
     */
    public function __construct(Imsi $originImsi, Imsi $virtualImsi)
    {
        list($this->_originImsi, $this->_virtualImsi) = [$originImsi, $virtualImsi];
        // Инициализация DirtyMsisdn's
        list($this->_dirtyOriginMsisdn, $this->_dirtyVirtualMsisdn) = [
            $this->_originImsi->msisdn, $this->_virtualImsi->msisdn
        ];
        // Журналирование операции инициализации
        $this->journal = [
            'worker' => (new \ReflectionClass($this))->getShortName(),
            'journal' => [
                'initialize' => [
                    'imsies' => [
                        'origin' => $originImsi->getAttributes(),
                        'virtual' => $virtualImsi->getAttributes(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws ModelValidationException
     */
    private function _localSyncOperation()
    {
        // Локальная синхронизацию Mysql
        $this->_localSyncMysql();
        // Локальная синхронизацию Postgres
        $this->_localSyncPostgres();
    }

    /**
     * Локальная синхронизацию Mysql
     * Процесс-задача: обменять между двумя моделями Number imsi
     *
     * @throws ModelValidationException
     */
    private function _localSyncMysql()
    {
        // Получение номеров из таблицы voip_numbers
        if (!$originNumber = Number::findOne(['number' => $this->_originImsi->msisdn])) {
            throw new InvalidParamException(sprintf('Не найден OriginNumber с msisdn: %d', $this->_originImsi->msisdn));
        }
        if (!$virtualNumber = Number::findOne(['number' => $this->_virtualImsi->msisdn])) {
            throw new InvalidParamException(sprintf('Не найден VirtualNumber с msisdn: %d', $this->_virtualImsi->msisdn));
        }

        // Обмен полями imsi
        list($originNumber->imsi, $virtualNumber->imsi) = [$virtualNumber->imsi, $originNumber->imsi];

        // Попытка сохранения моделей
        if (!$originNumber->save()) {
            throw new ModelValidationException($originNumber);
        }
        if (!$virtualNumber->save()) {
            throw new ModelValidationException($virtualNumber);
        }
    }

    /**
     * Локальная синхронизацию Postgres
     * Процесс-задача: обменять между двумя моделями Imsi msisdn
     * В случае возникновения исключительной ситуации будет произведен откат изменений
     *
     * @throws ModelValidationException
     */
    private function _localSyncPostgres()
    {
        // Производим обмен параметров MSISDN между оригинальной и виртуальной сим-картами без временной переменной
        list($this->_originImsi->msisdn, $this->_virtualImsi->msisdn) = [$this->_virtualImsi->msisdn, $this->_originImsi->msisdn];

        // Попытка сохранения моделей Imsi
        if (!$this->_originImsi->save()) {
            throw new ModelValidationException($this->_originImsi);
        }
        if (!$this->_virtualImsi->save()) {
            throw new ModelValidationException($this->_virtualImsi);
        }
        /**
         * Запись в response значений, которые поменялись.
         *  Массив будет передан контроллеру и отправлен в браузер, где JavaScript поменяет данные в формах
         */
        $this->response = [
            'origin_msisdn' => $this->_originImsi->msisdn,
            'virtual_msisdn' => $this->_virtualImsi->msisdn
        ];
    }

    /**
     * Глобальная синхронизация с MttApi
     * Процесс-задача: Обменять msisdn's между CustomerOrigin и CustomerVirtual
     *
     * @throws \yii\base\Exception
     */
    private function _globalSyncOperation()
    {
        /** @var MttApiMvnoConnector $mttApiMvnoConnector */
        $mttApiMvnoConnector = MttApiMvnoConnector::me();
        $tnsfMsisdn = $mttApiMvnoConnector->getTransferMsisdn();

        // Проверка доступности трансферного номера
        if (!$mttApiMvnoConnector->isMsisdnOpened($tnsfMsisdn)) {
            throw new LogicException(sprintf('Трансферный MSISDN %s занят', $tnsfMsisdn));
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
        // Проверка существования VirtualMsisdn в AccountData
        $this->_virtualAccountMvno = $mttApiMvnoConnector->getAccountData([
            'msisdn' => $this->_dirtyVirtualMsisdn
        ]);
        if ($this->_virtualAccountMvno->isEmpty) {
            throw new LogicException(sprintf('Аккаунт с virtualMsisdn %s отсутствует', $this->_dirtyVirtualMsisdn));
        }
        $this->journal['journal']['global_sync'][] = [
            'message' => 'Успешно получен virtualAccountMvno',
            'attributes' => $this->_virtualAccountMvno->getAttributes(),
            'status' => 'success',
        ];
        // В originAccountMvno освобождаем Origin MSISDN и заменяем его свободным Transfer MSISDN
        $updateCustomerOrigin = $mttApiMvnoConnector
            ->updateCustomerByMsisdn($this->_originAccountMvno->customer_name, $tnsfMsisdn);
        // Проверяем, что Origin MSISDN модели originAccountMvno освобожден и заменен на Transfer MSISDN
        if (!$mttApiMvnoConnector->isMsisdnOpened($this->_originAccountMvno->sip_id) || $mttApiMvnoConnector->isMsisdnOpened($tnsfMsisdn)) {
            // Журналирование критической ситуации
            $this->journal['journal']['global_sync'][] = [
                'message' => 'Ошибка обмена с originMsisdn на tsfMsisdn.',
                'status' => 'exception',
            ];
            throw new LogicException(json_encode($this->journal));
        }
        // В virtualAccountMvno освобождаем Virtual MSISDN и заменяем его освобожденным Origin MSISDN от originAccountMvno
        $updateCustomerVirtual = $mttApiMvnoConnector
            ->updateCustomerByMsisdn($this->_virtualAccountMvno->customer_name, $this->_originAccountMvno->sip_id);
        // Проверяем, что Virtual MSISDN модели virtualAccountMvno освобожден и заменен освобожденным Origin MSISDN модели originAccountMvno
        if ($mttApiMvnoConnector->isMsisdnOpened($this->_originAccountMvno->sip_id) || !$mttApiMvnoConnector->isMsisdnOpened($this->_virtualAccountMvno->sip_id)) {
            // Журналирование критической ситуации
            $this->journal['journal']['global_sync'][] = [
                'message' => 'Ошибка обмена с virtualMsisdn на originMsisdn.',
                'status' => 'exception',
            ];
            throw new LogicException(json_encode($this->journal));
        }
        // В originAccountMvno освобождаем Transfer MSISDN и заменяем его освобожденным Virtual MSISDN от virtualAccountMvno
        $updateCustomerOrigin = $mttApiMvnoConnector
            ->updateCustomerByMsisdn($this->_originAccountMvno->customer_name, $this->_virtualAccountMvno->sip_id);
        // Transfer MSISDN должен быть свободен
        if (!$mttApiMvnoConnector->isMsisdnOpened($tnsfMsisdn) || $mttApiMvnoConnector->isMsisdnOpened($this->_originAccountMvno->sip_id)) {
            // Журналирование критической ситуации
            $this->journal['journal']['global_sync'][] = [
                'message' => 'Ошибка обмена с virtualMsisdn на originMsisdn.',
                'status' => 'exception',
            ];
            throw new LogicException(json_encode($this->journal));
        }
        $this->journal['journal']['global_sync'][] = [
            'message' => 'Успешный обмен между originMsisdn и virtualMsisdn через tsfMsisdn.',
            'status' => 'success',
        ];
        Yii::info($this->journal, __METHOD__);
    }
}