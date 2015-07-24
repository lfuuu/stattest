<?php

namespace app\classes\behaviors;

use app\dao\ClientGridSettingsDao;
use app\models\ClientAccount;
use app\models\ClientContract;
use yii\base\Behavior;
use yii\db\ActiveRecord;


class SetOldStatus extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => "update",
            ActiveRecord::EVENT_AFTER_INSERT => "update"
        ];
    }

    public function update($event)
    {
        if ($event->sender instanceof ClientContract) {
            $bpStatus = ClientGridSettingsDao::me()->getGridByBusinessProcessStatusId($event->sender->business_process_status_id, false);

            if ($bpStatus && isset($bpStatus['oldstatus'])) {
                $this->setStatusForChildAccounts($event->sender, $bpStatus['oldstatus']);
            }
        }

        if ($event->sender instanceof ClientAccount && isset($event->changedAttributes["is_blocked"])) {
            if ($event->changedAttributes["is_blocked"] != $event->sender->is_blocked) {
                $newStatus = "debt";

                if (!$event->sender->is_blocked) {
                    $newStatus = "work";
                    $bpStatus = ClientGridSettingsDao::me()->getGridByBusinessProcessStatusId($event->sender->business_process_status_id, false);
                    if (isset($bpStatus['oldstatus']) && $bpStatus['oldstatus'])
                        $newStatus = $bpStatus['oldstatus'];
                }

                $event->sender->status = $newStatus;
                $event->sender->save();
            }
        }
    }

    private function setStatusForChildAccounts($model, $status)
    {
        foreach ($model->getAccounts() as $account) {
            $account->status = $status;
            $account->save();
        }
    }
}
