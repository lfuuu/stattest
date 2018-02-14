<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $account_id
 * @property int $server_id
 * @property string $amount_month
 * @property float $sum_month
 * @property string $amount_day
 * @property float $sum_day
 * @property string $amount_date
 * @property float $sum
 * @property float $sum_mn_day
 */
class StatsAccount extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.stats_account';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * Вернуть счетчики потраченных минут по пакетам
     *
     * @param int $clientAccountId
     * @param int $accountTariffId
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getStatsNnpPackageMinute($clientAccountId, $accountTariffId = null)
    {
        $sql = <<<SQL
SELECT
	at.account_tariff_id,
	p.name,
	COALESCE(stat.used_seconds, 0) AS used_seconds,
	TRUNC(at.coefficient * pm.minute * 60) AS total_seconds
FROM
	nnp.account_tariff_light at
INNER JOIN
	nnp.package p
	ON at.tariff_id = p.tariff_id
INNER JOIN
	nnp.package_minute pm
	ON p.tariff_id = pm.tariff_id
LEFT JOIN
	billing.stats_nnp_package_minute stat
	ON stat.nnp_account_tariff_light_id = at.id
	AND stat.nnp_package_minute_id = pm.id

WHERE
	at.account_client_id = :account_client_id
	AND at.deactivate_from > NOW()
SQL;
        $params = ['account_client_id' => $clientAccountId];

        if ($accountTariffId) {
            $sql .= ' AND at.account_tariff_id = :account_tariff_id';
            $params['account_tariff_id'] = $accountTariffId;
        }

        return self::getDb()
            ->createCommand($sql, $params)
            ->queryAll();

    }
}
