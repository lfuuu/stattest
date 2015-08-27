<?php

namespace app\classes\behaviors;

use app\models\Number;
use yii\base\Behavior;
use app\classes\Event;
use yii\db\ActiveRecord;


class ActualizeNumberByStatus extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'actualizeNumberByStatus',
            ActiveRecord::EVENT_AFTER_UPDATE => 'actualizeNumberByStatus'
        ];
    }

    public function actualizeNumberByStatus($event)
    {
        Number::dao()->actualizeStatusByE164($event->sender->E164);
    }
}
