<?php

namespace app\classes\adapters;

use app\classes\contragent\importer\lk\CoreLkContragent;
use app\classes\contragent\importer\lk\DataLoader;
use app\classes\event_bus_contragent\ContragentMessage;
use app\classes\Singleton;
use app\classes\Utils;
use RdKafka\Message;

class EventBusContragent extends Singleton
{
    const TOPIC = 'event_bus';

    public function listen()
    {
        EbcKafka::me()->getMessage(self::TOPIC, function (Message $message) {

            echo '. ';

            if (!$message->payload || !($payloadJson = Utils::fromJson($message->payload))) {
                echo PHP_EOL . date('r') . ': ' . 'Payload empty or invalid';
                return false;
            }

            print_r($message->payload);

            return (new ContragentMessage($message))->getOperator()->process();
        });
    }

    public function syncContragent($contragentId): bool
    {
        if (!$contragentId) {
            return false;
        }

        CoreLkContragent::syncDbRow($contragentId);

        $obj = DataLoader::getObjectsForSync($contragentId)->current();
        if ($obj) {
            $obj->getTransformatorByType()->update();
        }

        return true;
    }
}