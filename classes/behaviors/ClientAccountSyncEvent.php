<?php

namespace app\classes\behaviors;

use app\classes\Event;
use yii\db\ActiveRecord;
use yii\base\Behavior;

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
            Event::go($event->sender->is_blocked ? Event::ACCOUNT_BLOCKED : Event::ACCOUNT_UNBLOCKED, ['account_id' => $event->sender->id]);
        }
    }
}