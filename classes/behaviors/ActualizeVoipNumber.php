<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use app\classes\Event;
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
        Event::go('actualize_number', ['number' => $event->sender->E164]);
    }

    public function actualizeNumberAfterUpdate($event)
    {
        if (count($event->changedAttributes)) {
            Event::go('actualize_number', ['number' => $event->sender->E164]);
        }
    }

}
