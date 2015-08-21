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
            ActiveRecord::EVENT_BEFORE_INSERT => "actualizeNumberBeforeAdd",
            ActiveRecord::EVENT_BEFORE_UPDATE => "actualizeNumberBeforeUpdate"
        ];
    }

    public function actualizeNumberBeforeAdd($event)
    {
        Event::go('actualize_number', ['number' => $event->sender->E164]);
    }

    public function actualizeNumberBeforeUpdate($event)
    {
        if ($event->changedAttributes)
        {
            Event::go('actualize_number', ['number' => $event->sender->E164]);
        }
    }

}
