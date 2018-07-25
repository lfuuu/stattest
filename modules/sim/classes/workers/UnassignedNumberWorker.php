<?php

namespace app\modules\sim\classes\workers;

use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\sim\classes\MttApiMvnoConnector;
use app\modules\sim\models\Imsi;
use app\modules\sim\traits\OnionSyncTrait;
use LogicException;
use Yii;
use yii\base\InvalidParamException;

class UnassignedNumberWorker extends AbstractWorker implements WorkerInterface
{
    use OnionSyncTrait;

    /**
     * @var Imsi|null
     */
    private $_originImsi = null;

    /**
     * @var Number|null
     */
    private $_virtualNumber = null;

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
     * @param Number $virtualNumber
     */
    public function __construct(Imsi $originImsi, Number $virtualNumber)
    {
        list($this->_originImsi, $this->_virtualNumber) = [$originImsi, $virtualNumber];
        // Инициализация DirtyMsisdn's
        list($this->_dirtyOriginMsisdn, $this->_dirtyVirtualMsisdn) = [
            $this->_originImsi->msisdn, $this->_virtualNumber->number
        ];
        // Журналирование операции инициализации
        $this->journal = [
            'worker' => (new \ReflectionClass($this))->getShortName(),
            'journal' => [
                'initialize' => [
                    'origin_imsi' => $originImsi->getAttributes(),
                    'virtual_number' => $virtualNumber->getAttributes(),
                ],
            ],
        ];
    }

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
        // Обмен полями imsi
        list($originNumber->imsi, $this->_virtualNumber->imsi) = [$this->_virtualNumber->imsi, $originNumber->imsi];
        // Попытка сохранения моделей
        if (!$originNumber->save()) {
            throw new ModelValidationException($originNumber);
        }
        if (!$this->_virtualNumber->save()) {
            throw new ModelValidationException($this->_virtualNumber);
        }
    }

    /**
     * Локальная синхронизацию Postgres
     * Процесс-задача: заменить msisdn модели Imsi на VirtualNumber
     *
     * @throws ModelValidationException
     */
    private function _localSyncPostgres()
    {
        // База данных - Postgres. Замена MSISDN у OriginImsi на VirtualNumber
        $this->_originImsi->msisdn = $this->_virtualNumber->number;
        if (!$this->_originImsi->save()) {
            throw new ModelValidationException($this->_originImsi);
        }
        /**
         * Запись в response значений, которые поменялись.
         *  Массив будет передан контроллеру и отправлен в браузер, где JavaScript поменяет данные в формах
         */
        $this->response = [
            'origin_msisdn' => $this->_originImsi->msisdn,
            'virtual_number' => $this->_dirtyOriginMsisdn,
        ];
    }

    /**
     * Глобальная синхронизация с MttApi
     * Процесс-задача: Заменить msisdn в CustomerOrigin на VirtualMsisdn
     *
     * @throws \yii\base\Exception
     */
    private function _globalSyncOperation()
    {
        /** @var MttApiMvnoConnector $mttApiMvnoConnector */
        $mttApiMvnoConnector = MttApiMvnoConnector::me();
        // Проверка существования OriginMsisdn в AccountData
        $this->_originAccountMvno = $mttApiMvnoConnector->getAccountData([
            'msisdn' => $this->_dirtyOriginMsisdn
        ]);
        if ($this->_originAccountMvno->isEmpty) {
            throw new LogicException(sprintf('Аккаунт с originMsisdn %d отсутствует', $this->_dirtyOriginMsisdn));
        }
        $this->journal['journal']['global_sync'][] = [
            'message' => 'Успешно получен originAccountMvno',
            'attributes' => $this->_originAccountMvno->getAttributes(),
            'status' => 'success',
        ];
        // Проверяем, что  VirtualMsisdn модели Number не привязан
        if (!$mttApiMvnoConnector->isMsisdnOpened($this->_dirtyVirtualMsisdn)) {
            throw new LogicException(sprintf('Аккаунт с dirtyVirtualMsisdn %d занят', $this->_dirtyVirtualMsisdn));
        }
        $this->journal['journal']['global_sync'][] = [
            'message' => sprintf('Свободный dirtyVirtualMsisdn %s', $this->_dirtyVirtualMsisdn),
            'attributes' => $this->_dirtyVirtualMsisdn,
            'status' => 'success',
        ];
        // В originAccountMvno освобождаем Origin MSISDN и заменяем его свободным virtualMsisdn
        $updateCustomerOrigin = $mttApiMvnoConnector
            ->updateCustomerByMsisdn($this->_originAccountMvno->customer_name, $this->_dirtyVirtualMsisdn);
        // Проверяем, что Origin MSISDN модели originAccountMvno освобожден и заменен на virtualMsisdn
        if (!$mttApiMvnoConnector->isMsisdnOpened($this->_originAccountMvno->sip_id) || $mttApiMvnoConnector->isMsisdnOpened($this->_dirtyVirtualMsisdn)) {
            // Журналирование критической ситуации
            $this->journal['journal']['global_sync'][] = [
                'message' => 'Ошибка обмена с originMsisdn на dirtyVirtualMsisdn.',
                'status' => 'exception',
            ];
            throw new LogicException(json_encode($this->journal));
        }
        $this->journal['journal']['global_sync'][] = [
            'message' => 'Успешная замена между originMsisdn и dirtyVirtualMsisdn.',
            'status' => 'success',
        ];
        Yii::info($this->journal, __METHOD__);
    }
}