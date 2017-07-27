<?php

namespace app\models;

use app\classes\model\ActiveRecord;


/**
 * Class app\models\LkNotificationLog
 * Лог оповещений клиента
 *
 * @property
 * @property int $id
 * @property string $date
 * @property int $client_id
 * @property int $contact_id
 * @property string $event
 * @property int $is_set
 * @property float $balance
 * @property int $limit
 * @property float $value
 *
 * @package app\models
 */
class LkNotificationLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'lk_notice_log';
    }

    /*
    public static function contact_setEvent($contact, $fld, $value)
    {
        self::addLogRaw($contact->client_id, $contact->id, $fld, true, $contact->balance, $contact->{$fld}, $value);
    }

    public static function contact_unSetEvent($clientId, $fld, $data)
    {
        self::addLogRaw($clientId, 0, $fld, false, $data["balance"], $data["limit"], $data["value"]);
    }
*/

    public static function addLogRaw($clientId, $contactId, $event, $isSet, $balance, $limit, $value)
    {
        $row = new self;

        $row->client_id = $clientId;
        $row->contact_id = $contactId;
        $row->event = $event;
        $row->is_set = (int)$isSet;
        $row->balance = $balance;
        $row->limit = $limit;
        $row->value = $value;
        $row->save();
    }

}
