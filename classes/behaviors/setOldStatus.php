<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\ClientStatuses;
use app\models\ClientGridSettings;


class setOldStatus extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => "afterUpdate"
        ];
    }

    public function afterUpdate($event)
    {
        if ($event->changedAttributes)
        {
            if (isset($event->changedAttributes["business_process_status_id"]))
            {
                $bpStatus = ClientGridSettings::findOne($event->sender->business_process_status_id);

                if ($bpStatus && $bpStatus->oldstatus && $bpStatus->oldstatus != $event->sender->status)
                {
                    $event->sender->status = $bpStatus->oldstatus;
                    $event->sender->save();

                    $cs = new ClientStatuses();

                    $cs->ts = date("Y-m-d H:i:s");
                    $cs->id_client = $event->sender->id;
                    $cs->user = \Yii::$app->user->getIdentity()->user;
                    $cs->status = $bpStatus->oldstatus;
                    $cs->comment = "";

                    $cs->save();
                }
            }
        }
    }
}
