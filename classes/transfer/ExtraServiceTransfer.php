<?php

namespace app\classes\transfer;

use app\models\ClientAccount;
use app\models\UsageExtra;

class ExtraServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageExtra[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageExtra::find()
                ->innerJoinWith('tariff', false)
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->andWhere(['tarifs_extra.status' => ['public', 'special', 'archive']])
                ->andWhere(['NOT IN', 'tarifs_extra.code', ['welltime', 'wellsystem']])
                ->all();
    }

}