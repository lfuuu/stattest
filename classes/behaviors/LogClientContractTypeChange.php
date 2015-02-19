<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\ClientStatuses;
use app\models\ClientContractType;
use app\models\ClientBP;
use app\models\ClientGridSettings;


class LogClientContractTypeChange extends Behavior
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
            if (isset($event->changedAttributes["contract_type_id"]))
            {
                $cs = new ClientStatuses();

                $cs->ts = date("Y-m-d H:i:s");
                $cs->id_client = $event->sender->id;
                $cs->user = \Yii::$app->user->getIdentity()->user;
                $cs->status = "";
                $cs->comment = "Установлен тип договора: ".ClientContractType::findOne($event->sender->contract_type_id)->name;

                $cs->save();
            }

            if (isset($event->changedAttributes["business_process_id"]))
            {
                $cs = new ClientStatuses();

                $cs->ts = date("Y-m-d H:i:s");
                $cs->id_client = $event->sender->id;
                $cs->user = \Yii::$app->user->getIdentity()->user;
                $cs->status = "";
                $cs->comment = "Установлен бизнес процес: ".ClientBP::findOne($event->sender->business_process_id)->name;

                $cs->save();
            }

            if (isset($event->changedAttributes["business_process_status_id"]))
            {
                $cs = new ClientStatuses();

                $cs->ts = date("Y-m-d H:i:s");
                $cs->id_client = $event->sender->id;
                $cs->user = \Yii::$app->user->getIdentity()->user;
                $cs->status = "";
                $cs->comment = "Установлен статус бизнес процесса: ".  ClientGridSettings::findOne($event->sender->business_process_status_id)->name;

                $cs->save();
            }
        }
    }
}
