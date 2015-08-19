<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\UsageVoip;
use app\models\Region;

class UsageVoipAddress extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'setUsageAddress',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setUsageAddress',
        ];
    }

    public function setUsageAddress($event)
    {
        /** @var UsageVoip $usage */
        $usage = $event->sender;
        /** @var Region $region */
        $region = Region::findOne($usage->region);

        if (!$usage->address) {
            $usage->address = $region->datacenter->address;
            $usage->address_from_datacenter_id = $region->datacenter->id;
        }
        else {
            $usage->address_from_datacenter_id = null;
        }
    }

}