<?php

namespace app\modules\glpi\classes;

use app\classes\HttpClient;
use app\classes\HttpRequest;
use app\classes\Singleton;
use app\modules\glpi\models\Item;
use kartik\base\Config;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * @method static Glpi me($args = null)
 */
class Glpi extends Singleton
{

    protected $module = null;
    protected $sessionToken = '';


    /**
     * Инициализовать
     * Вызывается автоматически при создании singletone
     */
    public function init()
    {
        $this->module = Config::getModule('glpi');
    }

    /**
     * @param string $action
     * @return HttpRequest
     * @throws InvalidConfigException Если конфиг не заполнен
     */
    private function _getRequest($action)
    {
        $params = $this->module->params;
        $url = $params['url'];
        $appToken = $params['appToken'];
        if (!$url || !$appToken) {
            throw new InvalidConfigException('API GLPI was not configured');
        }

        return (new HttpClient)
            // ->addRequestFormatJson()
            ->addResponseFormatJson()
            ->createRequest()
            ->addHeaders(['Content-Type' => 'application/json'])
            ->addHeaders(['App-Token' => $appToken])
            ->setUrl($url . '/' . $action);
    }

    /**
     * Инициализировать сессию
     *
     * @throws InvalidConfigException Если конфиг не заполнен
     * @throws InvalidCallException Если сессия уже начата
     * @throws BadRequestHttpException Если сервер не ответил или неправильно ответил на API-запрос
     */
    public function initSession()
    {
        if ($this->sessionToken) {
            throw new InvalidCallException('SessionToken is empty');
        }

        $params = $this->module->params;

        $response = $this->_getRequest('initSession')
            ->setMethod('get')
            ->addHeaders(['Authorization' => 'user_token ' . $params['userToken']])
            ->send();
        $result = $response->data;
        if (!$response->getIsOk() || !is_array($result)) {
            throw new BadRequestHttpException(print_r($result, true));
        }

        if (!isset($result['session_token'])) {
            throw new BadRequestHttpException(print_r($result, true));
        }

        $this->sessionToken = $result['session_token'];
    }

    /**
     * Получить все заявки (проблемы)
     *
     * @return Item[]
     * @throws InvalidConfigException Если конфиг не заполнен
     * @throws InvalidCallException Если сессия не начата
     * @throws BadRequestHttpException Если сервер не ответил или неправильно ответил на API-запрос
     */
    public function getAllItems()
    {
        if (!$this->sessionToken) {
            throw new InvalidCallException('SessionToken is empty');
        }

        $response = $this->_getRequest('Problem/?expand_drodpowns=true')
            ->setMethod('get')
            ->addHeaders(['Session-Token' => $this->sessionToken])
            ->send();
        $result = $response->data;
        if (!$response->getIsOk() || !is_array($result)) {
            throw new BadRequestHttpException(print_r($result, true));
        }

        return Item::createFromArray($result);
    }

    /**
     * Удалить сессию
     *
     * @return bool
     * @throws InvalidConfigException Если конфиг не заполнен
     * @throws InvalidCallException Если сессия не начата
     * @throws BadRequestHttpException Если сервер не ответил или неправильно ответил на API-запрос
     */
    public function killSession()
    {
        if (!$this->sessionToken) {
            throw new InvalidCallException('SessionToken is empty');
        }

        $response = $this->_getRequest('killSession')
            ->setMethod('get')
            ->addHeaders(['Session-Token' => $this->sessionToken])
            ->send();
        $result = $response->data;
        if (!$response->getIsOk()) {
            throw new BadRequestHttpException(print_r($result, true));
        }

        // API в случае успеха ничего не возвращает. Только при ошибке
        if ($result) {
            throw new InvalidCallException(print_r($result, true));
        }

        $this->sessionToken = null;
        return true;
    }
}