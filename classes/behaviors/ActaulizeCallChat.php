<?php

namespace app\classes\behaviors;

use app\models\EventQueue;
use yii\base\Behavior;
use yii\db\ActiveRecord;


class ActaulizeCallChat extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => "actualizeCallChat",
            ActiveRecord::EVENT_AFTER_UPDATE => "actualizeCallChat"
        ];
    }

    public function actualizeCallChat($event)
    {
        if ($event->changedAttributes) {
            EventQueue::go(EventQueue::CHECK__CALL_CHAT);
        }
    }

}
