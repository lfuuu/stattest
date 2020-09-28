<?php

namespace app\modules\uu\resourceReader;

use app\modules\uu\models\AccountTariff;
use yii\db\Query;

class VoipPackageCallsResourceReader extends PackageCallsResourceReader
{
    /**
     * @param Query $query
     * @param AccountTariff $accountTariff
     */
    protected function andWhere(Query $query, AccountTariff $accountTariff)
    {
        $query->andWhere([
            'calls_price.account_id' => $accountTariff->client_account_id,
            'calls_price.number_service_id' => $accountTariff->prev_account_tariff_id, // основная услуга
        ]);

//        $query->andWhere(['<', 'calls_price.cost', 0]);
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 25887440, 'message' => 'Пересчет звонков за месяц'];
    }
}