<?php

namespace app\classes\important_events;

use DateTime;
use app\models\important_events\ImportantEvents;

abstract class ImportantEventsBuilder
{

    /**
     * @param int $clientId
     * @param string $eventType
     * @param float $balance
     * @param float $limit
     * @param float $currentValue
     * @param string $date
     * @return ImportantEvents
     * @throws \Exception
     */
    public static function create($clientId, $eventType, $balance, $limit = 0, $currentValue = 0, $date = 'now')
    {
        $event = new ImportantEvents;

        $event->client_id = $clientId;
        $event->date = (new DateTime($date))->format('Y-m-d H:i:s');
        $event->event = $eventType;
        $event->balance = $balance;
        $event->limit = $limit;
        $event->value = $currentValue;

        try {
            return $event->save();
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

}