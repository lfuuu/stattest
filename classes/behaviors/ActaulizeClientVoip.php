<?php

namespace app\classes\behaviors;

use app\models\ClientAccount;
use app\models\ContractType;
use app\models\Country;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\classes\Event;


class ActaulizeClientVoip extends Behavior
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

                Event::go("actualize_client", ["client_id" => $event->sender->id]);

            }
        }
    }

}
