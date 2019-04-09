<?php

namespace app\classes\adapters;

use app\classes\Singleton;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\InvalidConfigException;

/**
 * @method static ClientChangedAmqAdapter me($args = null)
 */
class ClientChangedAmqAdapter extends Singleton
{
    const EVENT = 'client_changed';

    // настройки
    private $_settings;

    /** @var AMQPStreamConnection */
    private $_connection;

    // Сообщение
    private $_messageContentType = 'text/plain';
    private $_messageDeliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT;

    /**
     * Инициализовать
     * Вызывается автоматически при создании singletone
     */
    public function init()
    {
        if (!isset(\Yii::$app->params['clientChangedAmqSettings']) || !\Yii::$app->params['clientChangedAmqSettings']) {
            throw new InvalidConfigException(self::class . '. Не настроен конфиг');
        }

        $this->_settings = \Yii::$app->params['clientChangedAmqSettings'];
    }

    /**
     * Вернуть connection
     *
     * @return AMQPStreamConnection
     * @throws \yii\base\InvalidConfigException
     */
    public function getConnection()
    {
        if ($this->_connection) {
            return $this->_connection;
        }

        if (!$this->isAvailable()) {
            throw new InvalidConfigException('Error. Не настроен конфиг');
        }

        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.connection
        $params = $this->_settings;
        $this->_connection = new AMQPStreamConnection(
            $params['host'],
            $params['port'],
            $params['user'],
            $params['pass'],
            $params['vhost']
        );
        return $this->_connection;
    }

    /**
     * Конфиг настроен?
     */
    public function isAvailable()
    {
        $params = $this->_settings;

        return $params
            && isset($params['host']) && $params['host']
            && isset($params['port']) && $params['port']
            && isset($params['user']) && $params['user']
            && isset($params['pass'])
            && isset($params['vhost']) && $params['vhost']
            && isset($params['queue']) && $params['queue'];
    }

    /**
     * Деструктор
     */
    public function __destruct()
    {
        if ($this->_connection) {
            $this->_connection->close();
        }
    }

    /**
     * Отправить сообщение в очередь
     *
     * @param string|array|object $messageBody
     * @throws \yii\base\InvalidConfigException
     */
    public function publishMessage($messageBody)
    {
        $params = $this->_settings;
        $queue = $params['queue'];

        if (is_array($messageBody) || is_object($messageBody)) {
            $messageBody = json_encode($messageBody);
        }

        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.channel
        $channel = $this->getConnection()->channel();

        // отправить сообщение
        $message = new AMQPMessage($messageBody, ['content_type' => $this->_messageContentType, 'delivery_mode' => $this->_messageDeliveryMode]);

        $channel->queue_declare($queue, false, true, false, false);

        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.publish
        $channel->basic_publish($message, '', $queue);

        // закрыть
        $channel->close();
    }
}