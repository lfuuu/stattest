<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\ClientStatuses;
use app\models\ClientGridSettings;


class checkIsActiveAccount extends Behavior
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

                if ($bpStatus && $bpStatus->oldstatus && $bpStatus->is_close_status == $event->sender->is_active) // is_close = !is_active
                {
                    $event->sender->is_active = $bpStatus->is_close_status ? 0 : 1;
                    $event->sender->is_blocked = 0;
                    $event->sender->save();

                    $cs = new ClientStatuses();

                    $cs->ts = date("Y-m-d H:i:s");
                    $cs->id_client = $event->sender->id;
                    $cs->user = \Yii::$app->user->getIdentity()->user;
                    $cs->status = "";
                    $cs->comment = "Лицевой счет " . ($event->sender->is_active ? "открыт" : "закрыт");

                    $cs->save();
                }
            }
        }
    }
}
