<?php

namespace app\dao\services;

use app\helpers\DateTimeZoneHelper;
use Yii;
use app\classes\Singleton;
use app\models\UsageTrunk;
use app\models\ClientAccount;

class TrunkServiceDao extends Singleton implements ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client)
    {
        $now = new \DateTime();

        return
            UsageTrunk::find()
                ->andWhere(['client_account_id' => $client->id])
                ->andWhere('actual_from <= :date', [':date' => $now->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

    public function hasService(ClientAccount $client)
    {
        return UsageTrunk::find()->where(['client_account_id' => $client->id])->count() > 0;
    }
}