<?php

namespace app\modules\async\classes;

use app\classes\Singleton;
use kartik\base\Config;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\InvalidConfigException;

/**
 * @method static AsyncAdapter me($args = null)
 */
class AsyncAdapter extends Singleton
{
    private $_module;

    /** @var AMQPStreamConnection */
    private $_connection;

    // Сообщение
    private $_messageContentType = 'application/json';
    private $_messageDeliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT;

    /**
     * Инициализовать
     * Вызывается автоматически при создании singletone
     */
    public function init()
    {
        $this->_module = Config::getModule('async');
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
        $params = $this->_module->params;
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
        $params = $this->_module->params;

        return $params
            && isset($params['host']) && $params['host']
            && isset($params['port']) && $params['port']
            && isset($params['user']) && $params['user']
            && isset($params['pass'])
            && isset($params['vhost']) && $params['vhost']
            ;
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
        $params = $this->_module->params;
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