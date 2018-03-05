<?php

namespace app\modules\uu\resourceReader;

use app\modules\uu\models\AccountTariff;
use yii\db\Query;

class TrunkCallsResourceReader extends PackageCallsResourceReader
{
    /**
     * @param Query $query
     * @param AccountTariff $accountTariff
     */
    protected function andWhere(Query $query, AccountTariff $accountTariff)
    {
        $query->andWhere([
            'calls_price.trunk_service_id' => $accountTariff->prev_account_tariff_id, // основная услуга
            'calls_price.number_service_id' => null,
        ]);
    }
}