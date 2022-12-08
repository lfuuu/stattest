<?php

namespace app\models\billing;

use app\classes\HttpClient;
use app\classes\model\ActiveRecord;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePricelist;
use app\modules\nnp\models\PackagePricelistNnpSms;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\Tariff;
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
    public static function getStatsNnpPackageMinute($clientAccountId, $accountTariffId = null, $isFiltred = true)
    {
        $accountTariffLightTableName = AccountTariffLight::tableName();
        $packageTableName = Package::tableName();
        $packageMinuteTableName = PackageMinute::tableName();
        $packagePricelistTableName = PackagePricelist::tableName();

        $sql = <<<SQL
SELECT
	at.id,
	at.account_tariff_id,
	at.account_package_id,
	p.name,
	COALESCE(stat.used_seconds, 0) AS used_seconds,
	TRUNC(at.coefficient * pm.minute * 60) AS total_seconds
FROM
	{$accountTariffLightTableName} at
INNER JOIN
	{$packageTableName} p
	ON at.tariff_id = p.tariff_id
INNER JOIN
	{$packageMinuteTableName} pm
	ON p.tariff_id = pm.tariff_id
LEFT JOIN
	billing.stats_nnp_package_minute stat
	ON stat.nnp_account_tariff_light_id = at.id
	AND stat.nnp_package_minute_id = pm.id

WHERE
	at.account_client_id = :account_client_id
	AND at.deactivate_from > NOW()
SQL;

        $sql2 = <<<SQL
SELECT
	at.id,
	at.account_tariff_id,
	at.account_package_id,
	p.name,
	COALESCE(stat.used_seconds, 0) AS used_seconds,
	TRUNC(at.coefficient * pm.minute * 60) AS total_seconds
FROM
	{$accountTariffLightTableName} at
INNER JOIN
	{$packageTableName} p
	ON at.tariff_id = p.tariff_id
INNER JOIN
	{$packagePricelistTableName} pm
	ON p.tariff_id = pm.tariff_id
LEFT JOIN
	billing.stats_nnp_package_pricelist_minute stat
	ON stat.nnp_account_tariff_light_id = at.id
	AND stat.nnp_package_pricelist_id = pm.id

WHERE
	at.account_client_id = :account_client_id
	AND at.deactivate_from > NOW()
SQL;

        $params = ['account_client_id' => $clientAccountId];

        if ($accountTariffId) {
            $sql .= ' AND at.account_tariff_id = :account_tariff_id';
            $sql2 .= ' AND at.account_tariff_id = :account_tariff_id';
            $params['account_tariff_id'] = $accountTariffId;
        }

        $mainSql = $sql . ' UNION ' . $sql2;

        $result = self::getDb()
            ->createCommand($mainSql, $params)
            ->queryAll();


        if ($isFiltred) {
            $result = array_filter($result, function ($row) {
                return isset($row['total_seconds']) && $row['total_seconds'] > 0;
            });
        }

        return $result;

    }


    /**
     * Вернуть счетчики потраченных sms по пакетам
     *
     * @param int $clientAccountId
     * @param int $accountTariffId
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getStatSms($clientAccountId, $accountTariffId = null)
    {
        $accountTariffLightTableName = AccountTariffLight::tableName();
        $packageTableName = Package::tableName();
        $packageSmsTableName = PackagePricelistNnpSms::tableName();

        $sql = <<<SQL
SELECT at.account_client_id,
       at.account_tariff_id,
       at.account_package_id,
       at.coefficient,
       t.name                                   as tariff_name,
       s.include_amount                         as amount,
       round(s.include_amount * at.coefficient) as amount_package,
       y.used_sms
FROM {$accountTariffLightTableName} at
INNER JOIN {$packageTableName} t on (t.tariff_id = at.tariff_id)
INNER JOIN {$packageSmsTableName} s on (s.tariff_id = at.tariff_id)
INNER JOIN billing.stats_package_yate_sms y on nnp_account_tariff_light_id = at.id
WHERE at.account_client_id = :account_client_id
  AND at.service_type_id = 17
  AND at.deactivate_from > NOW()
SQL;
        $params = [
            'account_client_id' => $clientAccountId,
        ];

        if ($accountTariffId) {
            $sql .= ' AND at.account_tariff_id = :account_tariff_id';
            $params['account_tariff_id'] = $accountTariffId;
        }

        $result = self::getDb()
            ->createCommand($sql, $params)
            ->queryAll();

        return $result;
    }

    public static function getStatInternet($number)
    {
        $url = isset(\Yii::$app->params['billerApiURL']) && \Yii::$app->params['billerApiURL'] ? \Yii::$app->params['billerApiURL'] : false;

        if (!$url) {
            return [];
        }

        try {
            $numberInfo = (new HttpClient())
                ->get($url . 'get.data_package', [
                    'did' => $number
                ])
                ->getResponseDataWithCheck();
        } catch (\Exception $e) {
            \Yii::error($e);
            return [];
        }

        return $numberInfo;
    }


    public static function getStatSms2($number)
    {
        /**
         * [
         *  {
         *   "account_tariff_light_id" : 43773691,
         *   "end_date" : "2022-12-31 21:00:00",
         *   "package_sms_id" : 93,
         *   "sms_amount" : 100,
         *   "sms_consumed" : 1,
         *   "start_date" : "2022-11-30 21:00:00",
         *   "tariff_id" : 14944
         *  }
         * ]
         */
        $url = isset(\Yii::$app->params['billerApiURL']) && \Yii::$app->params['billerApiURL'] ? \Yii::$app->params['billerApiURL'] : false;

        if (!$url || !\Yii::$app->isRus()) {
            return [];
        }

        try {
            $numberInfo = (new HttpClient())
                ->get($url . 'get.yate_sms_package', [
                    'did' => $number
                ])
                ->getResponseDataWithCheck();
        } catch (\Exception $e) {
            \Yii::error($e);
            throw $e;
            return [];
        }

        $result = [];
        if ($numberInfo) {
            foreach ($numberInfo as &$info) {
                $alt = AccountLogPeriod::find()->where(['id' => $info['account_tariff_light_id']])->asArray()->one();
                $coefficient = $alt ? $alt['coefficient'] : -1;
                $result[] = [
                    'tariff_name' => Tariff::find()->where(['id' => $info['tariff_id']])->select('name')->scalar(),
                    'account_tariff_id' => $alt ? $alt['account_tariff_id'] : 0,
                    'amount' => $info['sms_amount'],
                    'amount_package' => round($info['sms_amount'] / $coefficient),
                    'used_sms' => $info['sms_consumed'],
                    'coefficient' => $coefficient,
                    'tariff_id' => $info['tariff_id'],
                ];
            }
        }


        return $result;
    }
}
