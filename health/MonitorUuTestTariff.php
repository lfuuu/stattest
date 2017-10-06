<?php

namespace app\health;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;

/**
 * УУ. Услуга на тестовом тарифе не должна задерживаться надолго
 */
class MonitorUuTestTariff extends Monitor
{
    // позволим менеджерам увеличивать базовый срок тестового тарифа на несколько дней
    const DELTA_DAYS = 10;

    /**
     * Текущее значение
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $deltaDays = self::DELTA_DAYS;

        $testStatuses = implode(', ', Tariff::getTestStatuses());
        if (!$testStatuses) {
            return 0;
        }

        $sql = <<<SQL
            SELECT COUNT(*) as cnt
            FROM
                {$accountTariffTableName} account_tariff,
                {$tariffPeriodTableName} tariff_period,
                {$tariffTableName} tariff
            WHERE
                account_tariff.tariff_period_id = tariff_period.id
                AND tariff_period.tariff_id = tariff.id
                AND tariff.tariff_status_id IN ({$testStatuses})
                AND account_tariff.insert_time + INTERVAL tariff.count_of_validity_period day + INTERVAL {$deltaDays} day < NOW()
SQL;
        $db = AccountTariff::getDb();
        return $db->createCommand($sql)->queryScalar();
    }
}