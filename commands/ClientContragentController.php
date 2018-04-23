<?php

namespace app\commands;

use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use yii\console\Controller;

class ClientContragentController extends Controller
{
    /**
     * Сборка данных для колонки `created_at` модели ClientContragent
     * */
    public function actionRebuildDatetimeColumns()
    {
        $clientContragentTableName = ClientContragent::tableName();
        $clientContractTableName = ClientContract::tableName();
        $clientAccountTableName = ClientAccount::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();

        ClientContragent::getDb()
            ->createCommand("
                UPDATE
                  {$clientContragentTableName} contragent
                INNER JOIN (
                  SELECT
                    contract.contragent_id id,
                    MIN(account_tariff_log.actual_from_utc) min_date
                  FROM {$clientContractTableName} contract
                    INNER JOIN {$clientAccountTableName} client
                      ON client.contract_id = contract.id
                    INNER JOIN {$accountTariffTableName} account_tariff
                      ON client.id = account_tariff.client_account_id
                    INNER JOIN {$accountTariffLogTableName} account_tariff_log
                      ON account_tariff.id = account_tariff_log.account_tariff_id
                    INNER JOIN {$tariffPeriodTableName} period
                      ON account_tariff.tariff_period_id = period.id
                    INNER JOIN {$tariffTableName} tariff
                      ON period.tariff_id = tariff.id
                  WHERE tariff.tariff_status_id NOT IN (4, 9)
                  GROUP BY contract.contragent_id
                ) temporal ON contragent.id = temporal.id
                SET contragent.created_at = temporal.min_date
            ")
            ->execute();
    }

    /**
     * Удаление данных из колонки `created_at` модели ClientContragent
     */
    public function actionClearDatetimeColumns()
    {
        ClientContragent::updateAll([
            'created_at' => null,
        ]);
    }
}