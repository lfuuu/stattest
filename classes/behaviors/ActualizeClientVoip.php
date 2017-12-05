<?php

namespace app\classes\behaviors;

use app\models\EventQueue;
use yii\base\Behavior;
use yii\db\ActiveRecord;


class ActualizeClientVoip extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => "actualizeClientVoip"
        ];
    }

    public function actualizeClientVoip($event)
    {
        if (isset($event->changedAttributes["is_blocked"])) {
            if ($event->changedAttributes["is_blocked"] != $event->sender->is_blocked) {


                //TODO: need log event

                EventQueue::go(EventQueue::ACTUALIZE_CLIENT, ["client_id" => $event->sender->id]);

            }
        }
    }

}
