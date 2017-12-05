<?php

namespace app\classes\behaviors;

use app\models\EventQueue;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class ActualizeVoipNumber extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'actualizeNumberAfterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'actualizeNumberAfterUpdate',
        ];
    }

    public function actualizeNumberAfterInsert($event)
    {
        EventQueue::go(EventQueue::ACTUALIZE_NUMBER, ['number' => $event->sender->E164]);
    }

    public function actualizeNumberAfterUpdate($event)
    {
        if (count($event->changedAttributes)) {
            EventQueue::go(EventQueue::ACTUALIZE_NUMBER, ['number' => $event->sender->E164]);
        }
    }

}
