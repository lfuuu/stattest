<?php

namespace app\classes\adapters;

use app\classes\Assert;
use app\classes\HandlerLogger;
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
        if ($this->_settings) {
            return;
        }

        if (
            !isset(\Yii::$app->params['clientChangedAmqSettings'])
            || !\Yii::$app->params['clientChangedAmqSettings']
            || !\Yii::$app->params['clientChangedAmqSettings']['host']
            || !\Yii::$app->params['clientChangedAmqSettings']['user']
        ) {
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
     * Сообщение с изменениями по данному ЛС уже в очереди?
     *
     * @param integer $accountId
     * @return bool
     */
    public function isMessageExists($accountId)
    {
        if (!$accountId) {
            throw new \LogicException('accountId is false');
        }

        $isAccountIdFound = false;

        $queue = $this->_settings['queue'];

        $channel = $this->getConnection()->channel();

        list (, $queueLength,) = $channel->queue_declare($queue, false, true, false, false);

        if (!$queueLength) {
            // очередь пуста - нет сообщений
            return false;
        }

        $counter = 0;

        $callback = function (AMQPMessage $msg) use ($accountId, $queueLength, &$counter, &$isAccountIdFound) {

            $counter++;

            /** @var \PhpAmqpLib\Channel\AMQPChannel $channel */
            $channel = $msg->delivery_info['channel'];

            $msgArray = json_decode($msg->body, true);

            $isCancel = false;

            // все сообщения перебрали
            if ($counter >= $queueLength) {
                $isCancel = true;
            }

            // нашли accountId
            if (
                $msgArray
                && isset($msgArray['account_id'])
                && $msgArray['account_id'] == $accountId
            ) {
                $isAccountIdFound = true;
                $isCancel = true;
            }

            $channel->basic_nack($msg->delivery_info['delivery_tag'], false, true);

            if ($isCancel) {
                return $channel->basic_cancel($msg->delivery_info['consumer_tag']);
            }
        };


        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        $loopCounter = 0;
        while (count($channel->callbacks) && $loopCounter++ < 1000) {
            $channel->wait();
        }

        HandlerLogger::me()->add('Queue length: ' . $queueLength);

        $channel->close();

        return $isAccountIdFound;
    }

    /**
     * Обработка сообщения
     *
     * @param array $param
     */
    public function process($param)
    {
        Assert::isIndexExists($param, 'account_id');

        if (!$this->isMessageExists($param['account_id'])) {
            $this->publishMessage($param);
            HandlerLogger::me()->add('account_id: ' . $param['account_id'] . ' added');
            return;
        }

        HandlerLogger::me()->add('account_id: ' . $param['account_id'] . ' exists');
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