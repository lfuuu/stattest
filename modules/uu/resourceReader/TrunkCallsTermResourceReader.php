<?php

namespace app\modules\uu\resourceReader;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\resourceReader\PackageCallsResourceReader\TrafficParamsManager;
use yii\db\Query;

class TrunkCallsTermResourceReader extends PackageCallsResourceReader
{
    /**
     * @param Query $query
     * @param AccountTariff $accountTariff
     * @throws \yii\base\Exception
     */
    protected function andWhere(Query $query, AccountTariff $accountTariff)
    {
        if (!$accountTariff->isTestForOperationCost()) {
            // ничего не считаем
            $query->andWhere('1=0');
            return;
        }

        $params = TrafficParamsManager::me()->getTrafficParams($accountTariff);
        $query->andWhere([
            'calls_price.account_id' => $params->clientAccountId,
            'calls_price.trunk_service_id' => $params->prevAccountTariffId, // основная услуга
            'calls_price.number_service_id' => null,
        ]);

        $query->andWhere(['>', 'calls_price.cost', 0]);
    }
}