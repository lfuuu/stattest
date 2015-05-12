<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\LkWizardState;


class LkWizardClean extends Behavior
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
                if (!LkWizardState::isBPStatusAllow($event->sender->business_process_status_id, $event->sender->id))
                {
                    $wizard = LkWizardState::findOne($event->sender->id);

                    if ($wizard)
                        $wizard->delete();
                }
            }
        }
    }
}
