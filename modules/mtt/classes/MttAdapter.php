<?php

namespace app\modules\mtt\classes;

use app\classes\Event;
use app\classes\Singleton;
use app\modules\mtt\Module;
use kartik\base\Config;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\InvalidConfigException;

/**
 * @method static MttAdapter me($args = null)
 */
class MttAdapter extends Singleton
{
    const STATUS_OK = 'ok';

    /** @var Module */
    private $_module;

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
        $this->_module = Config::getModule('mtt');
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
            $params['HOST'],
            $params['PORT'],
            $params['USER'],
            $params['PASS'],
            $params['VHOST']
        );
        return $this->_connection;
    }

    /**
     * Конфиг настроен?
     */
    public function isAvailable()
    {
        $params = $this->_module->params;
        return $params['HOST'] && $params['PORT'] && $params['USER'] && $params['PASS'] && $params['VHOST'];
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
     * Отправить сообщение getAccountBalance в очередь
     *
     * Пример ответа:
     * {"requestId":"100123","method":"getAccountBalance","status":"ok","result":{"currency":"RUB","balance":"963.35142"}}
     *
     * @param string $msisdn
     * @param string $requestId
     * @throws \yii\base\InvalidConfigException
     */
    public function getAccountBalance($msisdn, $requestId)
    {
        $this->_getAccount('getAccountBalance', $msisdn, $requestId);
    }

    /**
     * Отправить сообщение getAccountData в очередь
     *
     * Пример ответа:
     * {"requestId":"100123","method":"getAccountData","status":"ok",
     * "result":{"data":{"i_product":"19179","activation_date":"2017-08-24","iso_639_1":"ru","iso_4217":"RUB","i_account":"105277438","blocked":"N","h323_password":"t95ac5d184403","i_lang":"ru","i_time_zone":"274",
     * customer_name":"5500910000000001320321","billing_model":"1","follow_me_enabled":"N","product_name":"MCN_telecom","sip_id":"79587980262"}}}
     *
     * @param string $msisdn
     * @param string $requestId
     * @throws \yii\base\InvalidConfigException
     */
    public function getAccountData($msisdn, $requestId)
    {
        $this->_getAccount('getAccountData', $msisdn, $requestId);
    }

    /**
     * @param string $method
     * @param string $msisdn
     * @param string $requestId
     * @throws \yii\base\InvalidConfigException
     */
    private function _getAccount($method, $msisdn, $requestId)
    {
        $message = [
            'requestId' => $requestId,
            'method' => $method,
            'parameters' => ['msisdn' => $msisdn],
        ];
        $this->publishMessage($message);
    }

    /**
     * Отправить сообщение в очередь
     *
     * @param string|array $messageBody
     * @link https://github.com/welltime/mtt-adapter
     * @throws \yii\base\InvalidConfigException
     */
    public function publishMessage($messageBody)
    {
        $params = $this->_module->params;
        $exchange = $params['INCOMING_EXCHANGE'];
        $queue = $params['INCOMING_QUEUE'];

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
        $params = $this->_module->params;
        $exchange = $params['OUTGOING_EXCHANGE'];
        $queue = $params['OUTGOING_QUEUE'];

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

        /** @var MttResponse $mttResponse */
        $mttResponse = json_decode($bodyStr, false);
        if (!$mttResponse) {
            echo 'Error. Не JSON.';
            return;
        }

        if ($mttResponse->status != self::STATUS_OK) {
            echo 'Error. Неправильный статус.';
            return;
        }

        // поставить в очередь Стата
        $mttResponse->result->requestId = $mttResponse->requestId;
        // EVENT_CALLBACK_GET_ACCOUNT_BALANCE, EVENT_CALLBACK_GET_ACCOUNT_DATA, EVENT_CALLBACK_BALANCE_ADJUSTMENT
        Event::go(Module::EVENT_PREFIX . $mttResponse->method, $mttResponse->result);

        // ACK
        /** @var AMQPChannel $channel */
        $channel = $msg->delivery_info['channel'];
        $deliveryTag = $msg->delivery_info['delivery_tag'];
        // @link http://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.ack
        $channel->basic_ack($deliveryTag);
    }
}