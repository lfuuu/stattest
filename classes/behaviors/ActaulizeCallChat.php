<?php

namespace app\classes\behaviors;

use app\models\ClientAccount;
use app\models\ContractType;
use app\models\Country;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\classes\Event;


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
            Event::go("check__call_chat");
        }
    }

}
