<?php

namespace app\modules\sim\classes;

use app\classes\HttpClient;
use app\classes\Singleton;
use app\modules\sim\models\AccountMvno;
use kartik\base\Config;

/**
 * Класс-обертка для обращения к Mvno-Connector с целью обмена MSISDN-номерами между клиентами
 * через трансферный номер
 */
class MttApiMvnoConnector extends Singleton
{
    private $_configParams = [];

    /**
     * @var string Константа названия метода, позволяющего получить информацию об абоненте
     */
    const METHOD_GET_ACCOUNT_DATA = 'getAccountData';

    /**
     * @var string Константа названия метода, позволяющего изменить параметры клиента и абонента
     */
    const METHOD_UPDATE_CUSTOMER = 'updateCustomer';

    public function init()
    {
        if (!$this->_configParams) {
            $moduleConfig = Config::getModule('sim');
            $this->_configParams = $moduleConfig->params;
        }
    }

    /**
     * @param string $method Вызываемый метод
     * @param array $params Параметры GET-запроса
     * @return array|mixed
     */
    private function _call($method, $params)
    {
        return (new HttpClient)
            ->createRequest()
            ->auth(['method' => 'bearer', 'token' => $this->_configParams['authorization'],])
            ->setUrl("{$this->_configParams['base_url']}/{$method}/?" . http_build_query($params))
            ->send()
            ->getData();
    }

    /**
     * Метод позволяет получить информацию об абоненте
     *
     * @param array $params
     * @return AccountMvno
     */
    public function getAccountData($params)
    {
        $response = $this->_call(self::METHOD_GET_ACCOUNT_DATA, $params);
        return new AccountMvno($response);
    }

    /**
     * Метод позволяет изменить параметры клиента и абонента
     *
     * @param array $params
     * @return AccountMvno
     */
    public function updateCustomer($params)
    {
        $response = $this->_call(self::METHOD_UPDATE_CUSTOMER, $params);
        return new AccountMvno($response);
    }

    /**
     * @return string Transfer MSISDN
     */
    public function getTransferMsisdn()
    {
        return $this->_configParams['transfer_msisdn'];
    }

    /**
     * Метод, проверяющий статус MSISDN на доступность
     *
     * @param string $msisdn
     * @return bool
     */
    public function isMsisdnOpened($msisdn)
    {
        $response = $this->_call(self::METHOD_GET_ACCOUNT_DATA, ['msisdn' => $msisdn]);
        $accountMvno = new AccountMvno($response);
        return $accountMvno->isEmpty;
    }
}