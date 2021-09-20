<?php

namespace app\modules\uu\tarificator;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use Yii;

/**
 * Предварительное списание (транзакции) минимальной платы за ресурсы. Тарификация
 */
class AccountLogMinTarificator extends Tarificator
{
    /**
     * Предварительное списание (транзакции) минимальной платы за ресурсы
     * Если указана услуга - только для нее, иначе для всех
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @throws \yii\db\Exception
     */
    public function tarificate($accountTariffId = null)
    {
        $db = Yii::$app->db;
        $accountLogMinTableName = AccountLogMin::tableName();

        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        $this->out('. ');

        $selectTmpSql = <<<SQL
            SELECT
                #fields#
            FROM {$tariffPeriodTableName} tariff_period,
                 {$accountLogPeriodTableName} account_log_period
            LEFT JOIN {$accountLogMinTableName} min on min.id = account_log_period.id
            WHERE
                account_log_period.tariff_period_id = tariff_period.id
                and min.id is null
SQL;

        if ($accountTariffId) {
            $selectTmpSql .= " AND account_log_period.account_tariff_id = {$accountTariffId}";

            $selectSumSql = str_replace('#fields#', 'SUM(tariff_period.price_min * account_log_period.coefficient) as sum_price', $selectTmpSql);

            $sum = (float)$db->createCommand($selectSumSql)->queryScalar();

            if (abs($sum) >= 0.01) {
                $this->isNeedRecalc = true;
            }
        }

        $fields = <<<SQL
                account_log_period.id,	
                account_log_period.date_from,
                account_log_period.date_to,
                account_log_period.tariff_period_id,
                account_log_period.account_tariff_id,
                null as account_entry_id,
                tariff_period.price_min as period_price,
                account_log_period.coefficient,
                tariff_period.price_min * account_log_period.coefficient as price
SQL;

        $selectMainSql = str_replace('#fields#', $fields, $selectTmpSql);


        $insertSql = <<<SQL
            INSERT INTO {$accountLogMinTableName} (
                id,	
                date_from,
                date_to,
                tariff_period_id,
                account_tariff_id,
                account_entry_id,
                period_price,
                coefficient,
                price
            )
            {$selectMainSql}
SQL;

        $db->createCommand($insertSql)
            ->execute();
        unset($insertSql);
    }
}
