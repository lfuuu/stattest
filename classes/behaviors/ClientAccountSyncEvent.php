<?php

namespace app\classes\behaviors;

use app\models\EventQueue;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class ClientAccountSyncEvent extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'AccountIsBlocked',
        ];
    }

    public function AccountIsBlocked($event)
    {
        if (
            isset($event->changedAttributes['is_blocked'])
            &&
            $event->changedAttributes['is_blocked'] != $event->sender->is_blocked
        ) {
            EventQueue::go($event->sender->is_blocked ? EventQueue::ACCOUNT_BLOCKED : EventQueue::ACCOUNT_UNBLOCKED, ['account_id' => $event->sender->id]);
        }
    }
}