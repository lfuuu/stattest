<?php 

use app\models\notifications\NotificationLog;

class LkNotificationLog 
{

    public static function contact_setEvent($contact, $fld, $value)
    {
        self::addLogRaw($contact->client_id, $contact->id, $fld, true, $contact->balance, $contact->{$fld}, $value);
    }

    public static function contact_unSetEvent($clientId, $fld, $data)
    {
        self::addLogRaw($clientId, 0, $fld, false, $data["balance"], $data["limit"], $data["value"]);
    }

    public static function addLogRaw($clientId, $contactId, $event, $isSet, $balance, $limit, $value)
    {
        global $db;

        $db->QueryInsert("lk_notice_log", array(
                    "client_id" => $clientId,
                    "contact_id" => $contactId,
                    "event" => $event,
                    "is_set" => $isSet ? "1" : "0",
                    "balance" => $balance,
                    "limit" => $limit,
                    "value" => $value
                    )
                );

        $notification = new NotificationLog;
        $notification->client_id = $clientId;
        $notification->date = (new DateTime('now'))->format('Y-m-d H:i:s');
        $notification->event = $event;
        $notification->balance = $balance;
        $notification->limit = $limit;
        $notification->value = $value;
        $notification->save();
    }

}

