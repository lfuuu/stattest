<?php

namespace app\classes\notifications;

use DateTime;
use app\models\notifications\NotificationLog;

abstract class NotificationBuilder
{

    /**
     * @param $clientId
     * @param $event
     * @param $balance
     * @param int $limit
     * @param string $currentValue
     * @param string $date
     * @return NotificationLog
     * @throws \Exception
     */
    public static function create($clientId, $event, $balance, $limit = 0, $currentValue = '', $date = 'now')
    {
        $notification = new NotificationLog;

        $notification->client_id = $clientId;
        $notification->date = (new DateTime($date))->format('Y-m-d H:i:s');
        $notification->event = $event;
        $notification->balance = $balance;
        $notification->limit = $limit;
        $notification->value = $currentValue;

        try {
            return $notification->save();
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

}