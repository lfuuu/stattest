<?php

namespace app\classes\adapters;

use app\classes\Singleton;
use yii\base\InvalidConfigException;

class EbcKafka extends Singleton
{
    const SSL_CA_CRT = '/etc/kafka-ssl/ca.crt';
    const SSL_TLS_CRT = '/etc/kafka-ssl/tls.crt';
    const SSL_TLS_KEY = '/etc/kafka-ssl/tls.key';

//    const SSL_CA_CRT = '/home/httpd/stat.mcn.ru/stat/config/kssl/cluster-ca.crt';
//    const SSL_TLS_CRT = '/home/httpd/stat.mcn.ru/stat/config/kssl/key/clients-ca.crt';
//    const SSL_TLS_KEY = '/home/httpd/stat.mcn.ru/stat/config/kssl/key/clients-ca.key';

    const DEFAULT_GROUPID = 'stat';
//    const DEFAULT_GROUPID = 'stat-test';

    const SEND_TIMEOUT = 10000;
    const DEFAULT_READ_TIMEOUT_SEC = 10;

    /** @var \RdKafka\Producer */
    private $producer = null;

    private $producerTopics = [];
    private $consumerTopics = [];

    public function isAvailable()
    {
        return isset(\Yii::$app->params['KAFKA_BROKERS'])
            && \Yii::$app->params['KAFKA_BROKERS']
            && file_exists(self::SSL_CA_CRT) && is_readable(self::SSL_CA_CRT)
            && file_exists(self::SSL_TLS_CRT) && is_readable(self::SSL_TLS_CRT)
            && file_exists(self::SSL_TLS_KEY) && is_readable(self::SSL_TLS_KEY);
    }

    private function getConfig($readerGroupId = null)
    {
        $config = new \RdKafka\Conf();
//        $config->set('debug', 'all');
//        $config->set('debug', 'consumer');
        $config->set('group.id', $readerGroupId ?: self::DEFAULT_GROUPID);
        $config->set('metadata.broker.list', \Yii::$app->params['KAFKA_BROKERS']);
        $config->set('client.id', $readerGroupId ?: self::DEFAULT_GROUPID);

        $config->set('auto.offset.reset', 'latest');
        $config->set('enable.partition.eof', 'true');

        if (\Yii::$app->params['IS_KAFKA_WITH_SSL'] ?? false) {
            $config->set('security.protocol', 'ssl');
            $config->set('ssl.ca.location', self::SSL_CA_CRT);
            $config->set('ssl.key.location', self::SSL_TLS_KEY);
            $config->set('ssl.certificate.location', self::SSL_TLS_CRT);
        }

        return $config;
    }

    private function getProducerTopic($topicName)
    {
        if (!$this->producer) {
            $this->producer = new \RdKafka\Producer($this->getConfig());
        }

        if (!isset($this->producerTopics[$topicName])) {
            $this->producerTopics[$topicName] = $this->producer->newTopic($topicName);
        }

        return $this->producerTopics[$topicName];
    }

    /**
     * @param $topicName
     * @return mixed|\RdKafka\kafkaConsumerTopic
     */
    public function getMessage($topicName, $callback, $timeoutSec = null, $readerGroupId = null)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('No callback function');
        }

        $timeoutSec = $timeoutSec ?: self::DEFAULT_READ_TIMEOUT_SEC;
        $readerGroupId = $readerGroupId ?: self::DEFAULT_GROUPID;

        if (!isset($this->consumerTopics[$topicName][$readerGroupId])) {
            $this->consumerTopics[$topicName][$readerGroupId] = new \RdKafka\KafkaConsumer($this->getConfig($readerGroupId));
        }

        $consumer = $this->consumerTopics[$topicName][$readerGroupId];

        $consumer->subscribe([$topicName]);

        while(true) {
            $message = $consumer->consume($timeoutSec * 10000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    echo "\n" . date("r") .": Message >>> \n";
                    $callback($message);
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "\n" . date("r") .": No more messages; will wait for more\n";
                    sleep(1);
                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "\n" . date("r") .": TIMED_OUT\n";
                    return;

                default:
                    throw new \Exception($message->errstr(), $message->err);

            }
        }
    }

    /**
     * @param string $topic
     * @param mixed $message
     * @param string $messageKey
     * @return bool
     * @throws InvalidConfigException
     */
    public function sendMessage($topic, $message, $key = null, $headers = null, $timestamp_ms=0): bool
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('Connect to Kafka not configured');
        }

        if (!$topic) {
            throw new \InvalidArgumentException('Topic is empty');
        }

        if (!$message) {
            throw new \InvalidArgumentException('Message is empty');
        }

        if (!is_string($message)) {
            $message = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $key = $key ?: md5(uniqid());

        $rdTopic = $this->getProducerTopic($topic);

        $result = $rdTopic->producev(RD_KAFKA_PARTITION_UA, 0, $message, $key, $headers, $timestamp_ms);

        $this->producer->flush(self::SEND_TIMEOUT);

        return (int) $result == RD_KAFKA_RESP_ERR_NO_ERROR;
    }
}
