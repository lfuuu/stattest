<?php

namespace app\classes;

use yii\base\InvalidConfigException;


/**
 * Класс отправки sms-сообщений
 * Интеграция со шлюзом на thiamis.mcn.ru
 * Class Smser
 */
class Smser
{
    private $_enterPoint = null;
    private $_client = null;
    private $_password = null;

    /**
     * Smser constructor
     *
     * @throws InvalidConfigException
     */
    public function __construct()
    {
        $this->_client = isset(\Yii::$app->params['sms_client']) ? \Yii::$app->params['sms_client'] : null;
        $this->_password = isset(\Yii::$app->params['sms_password']) ? \Yii::$app->params['sms_password'] : null;
        $this->_enterPoint = isset(\Yii::$app->params['sms_server']) ? \Yii::$app->params['sms_server'] : null;

        if (!$this->_client || is_null($this->_password) || !$this->_enterPoint || strpos($this->_enterPoint, 'http') !== 0) {
            throw new InvalidConfigException('Smser: bad config');
        }
    }

    /**
     * Отправка SMS-сообщения
     *
     * @param string $to
     * @param string $message
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     */
    public function send($to, $message)
    {
        $data = [
            "action" => "send",
            "client" => $this->_client,
            "phone" => $to,
            "message" => $message,
        ];

        $data["sign"] = md5($data["action"] . "|" . $data["client"] . "|" . $data["message"] . "|" . $data["phone"] . "|" . $this->_password);

        return $this->_send($data);
    }

    /**
     * Исполнительный механизм отправки сообщения
     *
     * @param array $data
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     */
    private function _send($data)
    {
        return (new HttpClient)
            ->createRequest()
            ->setMethod('get')
            ->setData($data)
            ->setUrl($this->_enterPoint)
            ->getResponseDataWithCheck();
    }

}