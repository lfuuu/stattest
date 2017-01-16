<?php

namespace app\classes;


/**
 * Класс отправки sms-сообщений
 * Интеграция со шлюзом на thiamis.mcn.ru
 * Class Smser
 * @package app\classes
 */
class Smser
{
    private $enterPoint = null;
    private $client = null;
    private $password = null;

    public function __construct()
    {
        $this->client = isset(\Yii::$app->params['sms_client']) ? \Yii::$app->params['sms_client'] : null;
        $this->password = isset(\Yii::$app->params['sms_password']) ? \Yii::$app->params['sms_password'] : null;
        $this->enterPoint = isset(\Yii::$app->params['sms_server']) ? \Yii::$app->params['sms_server'] : null;

        if (!$this->client || is_null($this->password) || !$this->enterPoint || strpos($this->enterPoint, 'http') !== 0) {
            throw new \Exception('Smser: bad config');
        }
    }

    /**
     * Отправка SMS-сообщения
     *
     * @param $to
     * @param $message
     * @return mixed
     */
    public function send($to, $message)
    {
        $data = [
            "action" => "send",
            "client" => $this->client,
            "phone" => $to,
            "message" => $message,
        ];

        $data["sign"] = md5($data["action"] . "|" . $data["client"] . "|" . $data["message"] . "|" . $data["phone"] . "|" . $this->password);

        return $this->_send($data);
    }

    /**
     * Исполнительный механизм отправки сообщения
     *
     * @param $data
     * @return mixed
     */
    private function _send($data)
    {
        $result = JSONQuery::exec($this->enterPoint, $data, false);

        if (isset($result["error"])) {
            throw new Exception($result["error"]);
        }

        return $result;
    }

}