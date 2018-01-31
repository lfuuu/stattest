<?php

namespace app\modules\mchs\classes\api;


use app\classes\HttpClient;
use app\classes\Singleton;
use kartik\base\Config;
use yii\base\InvalidConfigException;

/**
 * Class MvnoConnector
 *
 * @method static ApiMvnoConnector me($args = null)
 */
class ApiMvnoConnector extends Singleton
{
    private $_url = 'http://mvno-connector.mcn.ru/smsapi/mvno_mass_send/';

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return (bool)$this->_getConfigApiKey();
    }

    /**
     * @return string
     */
    private function _getConfigApiKey()
    {
        $_moduleConfig = Config::getModule('mchs');

        return isset($_moduleConfig->params['api_key']) && $_moduleConfig->params['api_key'] ? $_moduleConfig->params['api_key'] : null;
    }

    /**
     * Отправка сообщения
     *
     * @param string[] $phones
     * @param string $message
     * @return array
     * @throws InvalidConfigException
     */
    public function send($phones, $message)
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('MvnoConnector не настроен');
        }

        if (!is_array($phones)) {
            $phones = [$phones];
        }

        return (new HttpClient())
            ->createRequest()
            ->auth(['method' => 'bearer', 'token' => $this->_getConfigApiKey()])
            ->setMethod('post')
            ->setData(['message' => $message, 'phones' => $phones])
            ->setUrl($this->_url)
            ->getResponseDataWithCheck();
    }
}