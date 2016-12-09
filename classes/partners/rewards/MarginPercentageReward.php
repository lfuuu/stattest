<?php

namespace app\classes\partners\rewards;

use yii\db\Expression;
use yii\db\Query;
use app\classes\Assert;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\PartnerRewards;
use app\models\Transaction;
use app\models\UsageVoip;
use app\models\UsageTrunk;
use app\models\billing\ServiceNumber;
use app\models\billing\ServiceTrunk;
use app\models\billing\Calls;

abstract class MarginPercentageReward
{

    const REWARD_FIELD = 'percentage_of_margin';

    /**
     * @return string
     */
    public static function getField()
    {
        return self::REWARD_FIELD;
    }

    /**
     * @param PartnerRewards $reward
     * @param BillLine $line
     * @param array $settings
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings)
    {
        if (isset($settings[self::getField()])) {
            if ($line->bill->biller_version === ClientAccount::VERSION_BILLER_USAGE) {
                switch ($line->service) {
                    case Transaction::SERVICE_VOIP:
                        self::usageVoip($reward, $line, $settings[self::getField()]);
                        break;

                    case Transaction::SERVICE_TRUNK:
                        self::usageTrunk($reward, $line, $settings[self::getField()]);
                        break;
                }
            }
        }

    }

    /**
     * @param PartnerRewards $reward
     * @param BillLine $line
     * @param int $percentage
     * @throws \yii\base\Exception
     */
    private static function usageVoip(PartnerRewards $reward, BillLine $line, $percentage = 0)
    {
        $usage = UsageVoip::findOne(['id' => $line->id_service]);
        Assert::isObject($usage);

        $query  = new Query;

        $query->select([
            'service_number.did',
            'service_number.client_account_id',
            'calls_raw.number_service_id',
            'orig' => new Expression('SUM(CASE WHEN calls_raw.orig = true THEN -calls_raw.cost ELSE 0 END)'),
            'term' => new Expression('SUM(CASE WHEN calls_raw.orig = false THEN -calls_raw.cost ELSE 0 END)'),
        ]);

        $query->from(ServiceNumber::tableName());
        $query->leftJoin(Calls::tableName(), 'calls_raw.number_service_id = service_number.id');

        $query->where(new Expression('NOW() BETWEEN service_number.activation_dt AND service_number.expire_dt'));
        $query->andWhere(['>=', 'calls_raw.connect_time', $line->date_from]);
        $query->andWhere(['<=', 'calls_raw.connect_time', $line->date_to]);
        $query->andWhere(['service_number.did' => $usage->E164]);
        $query->andWhere(['service_number.client_account_id' => $line->bill->client_id]);

        $query->groupBy([
            'calls_raw.number_service_id',
            'service_number.did',
            'service_number.client_account_id',
        ]);

        $margin = 0;

        foreach ($query->each(1000, Calls::getDb()) as $marginRow) {
            if ($marginRow['client_account_id'] !== $line->bill->client_id) {
                continue;
            }
            $margin += $marginRow['orig'] - $marginRow['term'];
        }

        $reward->percentage_of_margin = $percentage * $margin / 100;
    }

    /**
     * @param PartnerRewards $reward
     * @param BillLine $line
     * @param int $percentage
     * @throws \yii\base\Exception
     */
    private static function usageTrunk(PartnerRewards $reward, BillLine $line, $percentage = 0)
    {
        $usage = UsageTrunk::findOne(['id' => $line->id_service]);
        Assert::isObject($usage);

        $query  = new Query;

        $query->select([
            'service_trunk.trunk_id',
            'service_trunk.client_account_id',
            'orig' => new Expression('SUM(CASE WHEN calls_raw.orig = true THEN -calls_raw.cost ELSE 0 END)'),
            'term' => new Expression('SUM(CASE WHEN calls_raw.orig = false THEN -calls_raw.cost ELSE 0 END)'),
        ]);

        $query->from(ServiceTrunk::tableName());
        $query->leftJoin(Calls::tableName(), 'calls_raw.trunk_id = service_trunk.trunk_id');

        $query->where(new Expression('NOW() BETWEEN service_trunk.activation_dt AND service_trunk.expire_dt'));
        $query->andWhere(['>=', 'calls_raw.connect_time', $line->date_from]);
        $query->andWhere(['<=', 'calls_raw.connect_time', $line->date_to]);
        $query->andWhere(['service_trunk.trunk_id' => $usage->trunk_id]);
        $query->andWhere(['service_trunk.client_account_id' => $line->bill->client_id]);

        $query->groupBy([
            'service_trunk.trunk_id',
            'service_trunk.client_account_id',
        ]);

        $margin = 0;

        foreach ($query->each(1000, Calls::getDb()) as $marginRow) {
            if ($marginRow['client_account_id'] !== $line->bill->client_id) {
                continue;
            }
            $margin += $marginRow['orig'] - $marginRow['term'];
        }

        $reward->percentage_of_margin = $percentage * $margin / 100;
    }

}