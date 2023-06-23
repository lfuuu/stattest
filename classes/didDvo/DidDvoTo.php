<?php

namespace app\classes\didDvo;

use app\classes\adapters\EbcKafka;

abstract class DidDvoTo
{
    protected array $regionList = [99];

    protected string $srcTopicName = "kafka-app1-DidForwarding-didForwarding-events";
    protected ?string $readerGroupId = null;

    public function go($isReset = 0)
    {
        EbcKafka::me()->getMessage($this->srcTopicName, function ($message) use ($isReset) {
            if (!$isReset) {
                $this->proccessMessage($message);
            }
        }, null, $this->readerGroupId);
    }

    abstract protected function proccessMessage($message, $isTest = false): bool;

    protected function checkMsgAndRegion($message): bool
    {
        if (!$message) {
            echo PHP_EOL . 'no msg';
            return false;
        }

        if (!isset($message['region_id'])) {
            echo PHP_EOL . 'no region in msg';
            return false;
        }

        if (!in_array($message['region_id'], $this->regionList)) {
            echo PHP_EOL . 'region: ' . $message['region_id'] . ' not in allowed list';
            return false;
        }

        return true;
    }


    public function test()
    {
        $payload = <<<JSON
{
  "did": "74958221132",
  "type": "uncond",
  "number": "+74958227732",
  "action": "on",
  "created_at": "2023-06-22T09:13:53.879128Z",
  "account_id": 130679,
  "did_forward_id": 1288486,
  "did_id": 502520,
  "region_id": 99,
  "service_id": 2479770
}
JSON;

        $message = (object)['payload' => $payload]; // as \RdKafka\Message
        return $this->proccessMessage($message, true);
    }
}