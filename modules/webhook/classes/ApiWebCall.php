<?php

namespace app\modules\webhook\classes;

use app\classes\HttpClient;
use app\classes\Singleton;
use kartik\base\Config;
use kartik\base\Module;
use yii\base\InvalidConfigException;

/**
 * @method static ApiWebCall me($args = null)
 */
class ApiWebCall extends Singleton
{
    /** @var Module */
    private $_module = null;

    private $_url = 'https://api.mcn.ru/v2/rest/account/::account_id::/vpbx/::vpbx_id::/outbound_call';

    const TIMEOUT_FROM = 30;
    const TIMEOUT_TO = 30;

    /**
     * Инициализация
     */
    public function init()
    {
        if (!$this->_module) {
            $this->_module = Config::getModule('webhook');
        }
    }

    private function _getParams()
    {
        $params = $this->_module->params['webcall'];

        return [
            'token' => isset($params['token']) ? $params['token'] : null,
            'account_id' => isset($params['account_id']) ? $params['account_id'] : null,
            'vpbx_id' => isset($params['vpbx_id']) ? $params['vpbx_id'] : null,
        ];
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        $params = $this->_module->params['webcall'];

        $token = $params['account_id'];
        $accountId = $params['account_id'];
        $vpbxId = $params['vpbx_id'];

        return $token && $accountId && $vpbxId;
    }

    /**
     * Сделать вызов
     *
     * @param int $abon
     * @param string $callerNumber
     * @return string
     * @throws InvalidConfigException
     */
    public function makeCall($abon, $callerNumber)
    {
        if (!$abon || !$callerNumber) {
            throw new \LogicException('Не указаны контакты');
        }

        if (!$this->isAvailable()) {
            throw new InvalidConfigException('Не настроен конфиг WebCall');
        }

        $params = $this->_getParams();

        $data = [
            'from' => $abon,
            'to' => $callerNumber,
            'timeout_from' => self::TIMEOUT_FROM,
            'timeout_to' => self::TIMEOUT_TO,
        ];

        $url = strtr($this->_url, [
            '::account_id::' => $params['account_id'],
            '::vpbx_id::' => $params['vpbx_id'],
        ]);


        $response = (new HttpClient)
            ->createJsonRequest()
            ->setUrl($url)
            ->setMethod('post')
            ->setData($data)
            ->auth(['method' => 'bearer', 'token' => $params['token']])
            ->setIsCheckOk(false)// если первый запрос обработался, но упал, то повторный отвечает ошибкой, но с нужными данными
            ->send();

      return $response->data;
    }

}