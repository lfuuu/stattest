<?php

namespace app\classes\adapters;

use app\classes\Singleton;
use app\exceptions\ModelValidationException;
use app\models\EventQueue;
use app\modules\mtt\classes\MttResponse;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\InvalidConfigException;

/**
 * @method static Tele2Adapter me($args = null)
 */
class Tele2Adapter extends Singleton
{
    const STATUS_OK = 'ok';
    const EVENT_PREFIX = 'Tele2_';
    const PREFIX = 'Tele2_';

    const PROFILE_NUMBER = 301;

    // Натройки подключения
    private $_settings;

    /** @var AMQPStreamConnection */
    private $_connection;

    // Очередь
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare
    //
    // true - подключаться только к уже существующей очереди с таким именем. Durable и AutoDelete игнорируются. Если такой очереди не существует - exception.
    // false - если нет такой, то создать новую. Если есть, то она должна быть такой же Durable и AutoDelete, иначе exception.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare.passive
    private $_queuePassive = true;

    // true - сохранять на HDD, чтобы не терять сообщения в случае падения RabbitMQ. Так надежнее (хотя и не 100%).
    // false - может быть в оперативной памяти. Так быстрее, но ненадежно.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare.durable
    private $_queueDurable = true;

    // true - используется только одним слушателем. Удалять очередь при его отключении. Запрет подключения других пользователей.
    // false - разные слушатели могут подключаться к очереди.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare.exclusive
    private $_queueExclusive = false;

    // true - удалять очередь, если нет ни одного слушателя.
    // false - хранить очередь, пока ее явно не удалят.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare.auto-delete
    private $_queueAutoDelete = false;

    // Точка доступа
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.exchange
    //
    // тип передачи сообщений
    // fanout — сообщение передаётся во все очереди.
    // direct — сообщение передаётся только в очередь с именем, совпадающим с ключом маршрутизации.
    // topic  — сообщение передаётся только в очереди, для которых совпадает маска на ключ маршрутизации.
    private $_exchangeType = 'fanout';

    // true - подключаться только к уже существующей очереди с таким именем. Durable и AutoDelete игнорируются. Если такой очереди не существует - exception.
    // false - если нет такой, то создать новую. Если есть, то она должна быть такой же Durable и AutoDelete, иначе exception.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.declare.passive
    private $_exchangePassive = true;

    // true - сохранять на HDD, чтобы не терять сообщения в случае падения RabbitMQ. Так надежнее (хотя и не 100%).
    // false - может быть в оперативной памяти. Так быстрее, но ненадежно.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.declare.durable
    private $_exchangeDurable = true;

    // true - удалять очередь, если нет ни одного слушателя.
    // false - хранить очередь, пока ее явно не удалят.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.declare.auto-delete
    private $_exchangeAutoDelete = false;

    // Сообщение
    private $_messageContentType = 'text/plain';
    private $_messageDeliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT;

    // Слушатель
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume
    //
    // ID слушателя.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume.consumer-tag
    private $_consumerTag = '';

    // true - сервер не будет отправлять слушателю те сообщения, которые он (слушатель) сам же и опубликовал.
    // false - отправлять слушателю все сообщения.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume.no-local
    private $_consumerNoLocal = false;

    // true - ACK автоматически после отправки сообщения слушателю, так быстрее, но ненадежно (если слушатель получил, но упал, не успев обработать).
    // false - требуется явный ACK от слушателя. Пока его нет - сервер будет отправлять это сообщение по разу каждому слушателю, включая этого после перезапуска.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume.no-ack
    private $_consumerNoAck = false;

    // true - используется только одним слушателем. Удалять очередь при его отключении. Запрет подключения других пользователей.
    // false - разные слушатели могут подключаться к очереди.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume.exclusive
    private $_consumerExclusive = false;

    // true - не ждать ответа сервера. Так быстрее, но возможен exception.
    // false - ждать ответа. Так надежнее.
    // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume.no-wait
    private $_consumerNoWait = false;

    /**
     * Инициализовать
     * Вызывается автоматически при создании singletone
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->_settings = isset(\Yii::$app->params['Tele2AmqSettings']) ? \Yii::$app->params['Tele2AmqSettings'] : null;
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

        if (!$params) {
            return false;
        }

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
     * Api method 'addGprs' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function addGprs($requestId, $imsi)
    {
        return $this->_exec('addGprs', $requestId, $imsi);
    }


    /**
     * Api method 'addSubscriber' need parameters:
     * - imsi, type numeric string
     * - msisdn, type numeric string
     * - profileNumber, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @param string $msisdn
     * @return string
     * @internal param string $msisdn
     */
    public function addSubscriber($requestId, $imsi, $msisdn)
    {
        return $this->_exec('addSubscriber', $requestId, $imsi, ['msisdn' => $msisdn, 'profileNumber' => self::PROFILE_NUMBER]);
    }

    /**
     * Api method 'blockIncoming' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function blockIncoming($requestId, $imsi)
    {
        return $this->_exec('blockIncoming', $requestId, $imsi);
    }

    /**
     * Api method 'blockRoamingInternational' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function blockRoamingInternational($requestId, $imsi)
    {
        return $this->_exec('blockRoamingInternational', $requestId, $imsi);
    }

    /**
     * Api method 'blockOutgoing' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function blockOutgoing($requestId, $imsi)
    {
        return $this->_exec('blockOutgoing', $requestId, $imsi);
    }

    /**
     * Api method 'deleteSubscriber' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function deleteSubscriber($requestId, $imsi)
    {
        return $this->_exec('deleteSubscriber', $requestId, $imsi);
    }

    /**
     * Api method 'getSubscriberStatus' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function getSubscriberStatus($requestId, $imsi)
    {
        return $this->_exec('getSubscriberStatus', $requestId, $imsi);
    }

    /**
     * Api method 'changeImsi' need parameters:
     * - imsi, type numeric string
     * - newImsi, type numeric string
     * - msisdn, type numeric string
     * - esn, type numeric string (random ???)
     *
     * @param string $requestId
     * @param string $imsi
     * @param string $newImsi
     * @param string $msisdn
     * @return string
     * @internal param string $msisdn
     */
    public function changeImsi($requestId, $imsi, $newImsi, $msisdn)
    {
        return $this->_exec('changeImsi', $requestId, $imsi, ['newImsi' => $newImsi, 'msisdn' => $msisdn, 'esn' => rand(1000, 9999)]);
    }

    /**
     * Api method 'changeMsisdn' need parameters:
     * - imsi, type numeric string
     * - msisdn, type numeric string
     * - oldMsisdn, type numeric string
     * - esn, type numeric string (random ???)
     *
     * @param string $requestId
     * @param string $imsi
     * @param string $oldMsisdn
     * @param string $newMsisdn
     * @return string
     * @internal param string $msisdn
     */
    public function changeMsisdn($requestId, $imsi, $oldMsisdn, $newMsisdn)
    {
        return $this->_exec('addGprs', $requestId, $imsi, ['msisdn' => $newMsisdn, 'oldMsisdn' => $oldMsisdn, 'esn' => rand(1000, 9999)]);
    }

    /**
     * Api method 'removeGprs' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function removeGprs($requestId, $imsi)
    {
        return $this->_exec('removeGprs', $requestId, $imsi);
    }

    /**
     * Api method 'unblockIncoming' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function unblockIncoming($requestId, $imsi)
    {
        return $this->_exec('unblockIncoming', $requestId, $imsi);
    }

    /**
     * Api method 'unblockOutgoing' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function unblockOutgoing($requestId, $imsi)
    {
        return $this->_exec('unblockOutgoing', $requestId, $imsi);
    }

    /**
     * Api method 'unblockRoamingInternational' need parameters:
     * - imsi, type numeric string
     * and optional parameters:
     * - msisdn, type numeric string
     *
     * @param string $requestId
     * @param string $imsi
     * @return string
     * @internal param string $msisdn
     */
    public function unblockRoamingInternational($requestId, $imsi)
    {
        return $this->_exec('unblockRoamingInternational', $requestId, $imsi);
    }


    /**
     * @param string $method
     * @param string $requestId
     * @param string $imsi
     * @param array $parameters
     * @return string
     * @internal param string $msisdn
     */
    private function _exec($method, $requestId, $imsi, $parameters = [])
    {
        $message = [
            'requestId' => self::PREFIX . $requestId,
            'method' => $method,
            'parameters' => ['imsi' => $imsi] + $parameters,
        ];

        $this->publishMessage($message);

        return print_r($message, true);
    }

    /**
     * Отправить сообщение в очередь
     *
     * @param string|array|object $messageBody
     * @link https://github.com/welltime/mtt-adapter
     * @throws \yii\base\InvalidConfigException
     */
    public function publishMessage($messageBody)
    {
        $params = $this->_settings;
        $exchange = $params['exchangeRequest'];
        $queue = $params['queueRequest'];

        if (is_array($messageBody) || is_object($messageBody)) {
            $messageBody = json_encode($messageBody);
        }

        // очередь
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.channel
        $channel = $this->getConnection()->channel();
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.queue
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare
        $channel->queue_declare($queue, $this->_queuePassive, $this->_queueDurable, $this->_queueExclusive, $this->_queueAutoDelete);

        // точка доступа
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.exchange
        $channel->exchange_declare($exchange, $this->_exchangeType, $this->_exchangePassive, $this->_exchangeDurable, $this->_exchangeAutoDelete);
        $channel->queue_bind($queue, $exchange);

        // отправить сообщение
        $message = new AMQPMessage($messageBody, ['content_type' => $this->_messageContentType, 'delivery_mode' => $this->_messageDeliveryMode]);
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.publish
        $channel->basic_publish($message, $exchange);

        // закрыть
        $channel->close();
    }

    /**
     * Запустить слушатель
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \PhpAmqpLib\Exception\AMQPOutOfBoundsException
     * @throws \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function runReceiverDaemon()
    {
        $params = $this->_settings;
        $exchange = $params['exchangeResponse'];
        $queue = $params['queueResponse'];

        // очередь
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.channel
        $channel = $this->getConnection()->channel();
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.queue
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare
        $channel->queue_declare($queue, $this->_queuePassive, $this->_queueDurable, $this->_queueExclusive, $this->_queueAutoDelete);

        // точка доступа
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.exchange
        $channel->exchange_declare($exchange, $this->_exchangeType, $this->_exchangePassive, $this->_exchangeDurable, $this->_exchangeAutoDelete);
        $channel->queue_bind($queue, $exchange);

        // слушать
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume
        $channel->basic_consume($queue, $this->_consumerTag, $this->_consumerNoLocal, $this->_consumerNoAck, $this->_consumerExclusive, $this->_consumerNoWait, [$this, 'receiverCallback']);
        while (count($channel->callbacks)) {
            $channel->wait();
        }

        // закрыть
        $channel->close();
    }

    /**
     * @param AMQPMessage $msg
     * @throws \app\exceptions\ModelValidationException
     */
    public function receiverCallback(\PhpAmqpLib\Message\AMQPMessage $msg)
    {
        $bodyStr = $msg->body;

        echo date(DATE_ATOM) . ' ' . print_r($bodyStr, true) . PHP_EOL;

        // ACK
        /** @var AMQPChannel $channel */
        $channel = $msg->delivery_info['channel'];
        $deliveryTag = $msg->delivery_info['delivery_tag'];
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.ack
        $channel->basic_ack($deliveryTag);


        $response = json_decode($bodyStr, true);
        if (!$response) {
            echo 'Error. Не JSON.';
            return;
        }

        if (!isset($response['requestId'])) {
            echo 'Error. requestId не найден';
            return;
        }

        if (strpos($response['requestId'], self::PREFIX) === 0) {
            $response['requestId'] = str_replace(self::PREFIX, '', $response['requestId']);
        } else {
            echo 'Error. Unknown requestId';
            return;
        }

        $event = EventQueue::findOne(['id' => $response['requestId']]);

        $event && isset($response['result']) && $event->trace .= json_encode($response['result']) . PHP_EOL;


        if (
            $response['status'] != self::STATUS_OK
            && (
                strpos($response['result'], 'IMSI ALREADY DEFINED') !== false
                || strpos($response['result'], 'Subscriber want been found') !== false
                || strpos($response['result'], 'SUBSCRIBER AUTHENTICATION DATA NOT FOUND') !== false
            )
        ) {
            echo 'Error. Неправильный статус.';
            $event && $event->status = EventQueue::STATUS_STOP;
        }

        if (!$event) {
            echo 'Error. Задание в очереди не найдено.';
            return;
        }

        if (!$event->save()) {
            throw new ModelValidationException($event);
        }
    }
}