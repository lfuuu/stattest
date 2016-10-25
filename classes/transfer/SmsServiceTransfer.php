<?php

namespace app\classes\transfer;

use app\models\ClientAccount;
use app\models\UsageSms;

class SmsServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageSms[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageSms::find()
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->all();
    }

}