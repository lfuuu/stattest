<?php

namespace app\classes\adapters;

use app\classes\Singleton;
use yii\base\InvalidConfigException;

class EbcKafka extends Singleton
{
    const SSL_CA_CRT = '/etc/kafka-ssl/ca.crt';
    const SSL_TLS_CRT = '/etc/kafka-ssl/tls.crt';
    const SSL_TLS_KEY = '/etc/kafka-ssl/tls.key';

    const DEFAULT_GROUPID = 'group-stat';

    const SEND_TIMEOUT = 10000;

    /** @var \RdKafka\Producer */
    private $producer = null;

    private $rdTopics = [];

    public function isAvailable()
    {
        return isset(\Yii::$app->params['KAFKA_BROKERS'])
            && \Yii::$app->params['KAFKA_BROKERS']
            && file_exists(self::SSL_CA_CRT) && is_readable(self::SSL_CA_CRT)
            && file_exists(self::SSL_TLS_CRT) && is_readable(self::SSL_TLS_CRT)
            && file_exists(self::SSL_TLS_KEY) && is_readable(self::SSL_TLS_KEY);
    }

    private function getConfig()
    {
        $config = new \RdKafka\Conf();
//        $config->set('debug', 'all');
        $config->set('group.id', self::DEFAULT_GROUPID);
        $config->set('metadata.broker.list', \Yii::$app->params['KAFKA_BROKERS']);
        $config->set('client.id', self::DEFAULT_GROUPID);

        if (\Yii::$app->params['IS_KAFKA_WITH_SSL'] ?? false) {
            $config->set('security.protocol', 'ssl');
            $config->set('ssl.ca.location', self::SSL_CA_CRT);
            $config->set('ssl.key.location', self::SSL_TLS_KEY);
            $config->set('ssl.certificate.location', self::SSL_TLS_CRT);
        }

        $this->producer = new \RdKafka\Producer($config);
    }

    private function getRdTopic($topicName)
    {
        if (!$this->producer) {
            $this->getConfig();
        }

        if (!isset($this->rdTopics[$topicName])) {
            $this->rdTopics[$topicName] = $this->producer->newTopic($topicName);
        }

        return $this->rdTopics[$topicName];
    }

    /**
     * @param string $topic
     * @param mixed $message
     * @param string $messageKey
     * @return bool
     * @throws InvalidConfigException
     */
    public function sendMessage($topic, $message, $key = null): bool
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

        $rdTopic = $this->getRdTopic($topic);

        $result = $rdTopic->produce(RD_KAFKA_PARTITION_UA, 0, $message, $key ?: md5(uniqid()));
        $this->producer->flush(self::SEND_TIMEOUT);

        return (int) $result == RD_KAFKA_RESP_ERR_NO_ERROR;
    }
}