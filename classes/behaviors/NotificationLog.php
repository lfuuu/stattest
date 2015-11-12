<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\notifications\NotificationContactLog;
use app\models\ClientContact;

class NotificationLog extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'setNotificationContacts',
        ];
    }

    public function setNotificationContacts($event)
    {
        /** @var \app\models\notifications\NotificationLog $notification */
        $notification = $event->sender;

        $contacts =
            ClientContact::find()
                ->where(['client_id' => $notification->client_id])
                ->andWhere(['is_active' => 1])
                ->andWhere(['is_official' => 1])
                ->andWhere(['in', 'type', ['email', 'sms']])
                ->all();

        foreach ($contacts as $contact) {
            $record = new NotificationContactLog;
            $record->date = $notification->date;
            $record->contact_id = $contact->id;
            $record->notification_id = $notification->id;
            $record->status = 0;
            $record->save();
        }
    }

}