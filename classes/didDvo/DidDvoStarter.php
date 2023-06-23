<?php

namespace app\classes\didDvo;

use app\classes\adapters\EbcKafka;
use app\classes\Utils;

class DidDvoStarter extends DidDvoTo
{
    protected string $srcTopicName = "kafka-app1-DidForwarding-didForwarding";
    protected string $dstTopicName = "kafka-app1-DidForwarding-didForwarding-events";

    public function proccessMessage($message, $isTest = false): bool
    {
        var_dump($message);
        $d = Utils::fromJson($message->payload);

        $fwdNumber = $d['number'];
        if (strpos($fwdNumber, '@') !== false) {
            return false;
        }

        $helper = DidDvoHelper::me();

        $d['region_id'] = $helper->getRegion($d['did']);
        $d['service_id'] = $helper->getServiceId($d['account_id'], $d['did']);

        if (!$isTest) {
            EbcKafka::me()->sendMessage($this->dstTopicName, $d, $d['did'], null, $message->timestamp);
        }

        print_r($d);
        echo "<<< ";

        return true;
    }

    public function test(): void
    {
        $payload = <<<LOAD
{
  "did": "74958221132",
  "type": "uncond",
  "number": "+74958227732",
  "action": "on",
  "created_at": "2023-06-22T09:13:53.879128Z",
  "account_id": 130679,
  "did_forward_id": 1288486,
  "did_id": 502520
}
LOAD;

        $msg = (object)['payload' => $payload];
        $this->proccessMessage($msg, true);
    }

}