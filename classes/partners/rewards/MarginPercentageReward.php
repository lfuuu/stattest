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
use app\models\billing\CallsRaw;

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
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function calculate(PartnerRewards $reward, BillLine $line, array $settings)
    {
        if (!array_key_exists(self::getField(), $settings)) {
            return false;
        }

        switch ($line->bill->biller_version) {

            case ClientAccount::VERSION_BILLER_USAGE: {
                switch ($line->service) {
                    case Transaction::SERVICE_VOIP:
                        self::processVoipService($reward, $line, $settings[self::getField()]);
                        break;

                    case Transaction::SERVICE_TRUNK:
                        self::processVoipTrunkService($reward, $line, $settings[self::getField()]);
                        break;
                }
                break;
            }

            case ClientAccount::VERSION_BILLER_UNIVERSAL: {
                // @todo universal service
                break;
            }

        }

        return true;
    }

    /**
     * @param PartnerRewards $reward
     * @param BillLine $line
     * @param int $percentage
     * @throws \yii\base\Exception
     */
    private static function processVoipService(PartnerRewards $reward, BillLine $line, $percentage = 0)
    {
        /** @var UsageVoip $service */
        $service = UsageVoip::findOne(['id' => $line->id_service]);
        Assert::isObject($service);

        $query  = (new Query)
            ->select([
                'service_number.did',
                'service_number.client_account_id',
                'calls_raw.number_service_id',
                'orig' => new Expression('SUM(CASE WHEN calls_raw.orig = true THEN -calls_raw.cost ELSE 0 END)'),
                'term' => new Expression('SUM(CASE WHEN calls_raw.orig = false THEN -calls_raw.cost ELSE 0 END)'),
            ])

            ->from(ServiceNumber::tableName())
            ->leftJoin(CallsRaw::tableName(), 'calls_raw.number_service_id = service_number.id')

            ->where(new Expression('NOW() BETWEEN service_number.activation_dt AND service_number.expire_dt'))
            ->andWhere(['>=', 'calls_raw.connect_time', $line->date_from])
            ->andWhere(['<=', 'calls_raw.connect_time', $line->date_to])
            ->andWhere(['service_number.did' => $service->E164])
            ->andWhere(['service_number.client_account_id' => $line->bill->client_id])

            ->groupBy([
                'calls_raw.number_service_id',
                'service_number.did',
                'service_number.client_account_id',
            ]);

        $margin = 0;

        foreach ($query->each(1000, CallsRaw::getDb()) as $marginRow) {
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
    private static function processVoipTrunkService(PartnerRewards $reward, BillLine $line, $percentage = 0)
    {
        /** @var UsageTrunk $service */
        $service = UsageTrunk::findOne(['id' => $line->id_service]);
        Assert::isObject($service);

        $query = (new Query)
            ->select([
                'service_trunk.trunk_id',
                'service_trunk.client_account_id',
                'orig' => new Expression('SUM(CASE WHEN calls_raw.orig = true THEN -calls_raw.cost ELSE 0 END)'),
                'term' => new Expression('SUM(CASE WHEN calls_raw.orig = false THEN -calls_raw.cost ELSE 0 END)'),
            ])
            ->from(ServiceTrunk::tableName())
            ->leftJoin(CallsRaw::tableName(), 'calls_raw.trunk_id = service_trunk.trunk_id')

            ->where(new Expression('NOW() BETWEEN service_trunk.activation_dt AND service_trunk.expire_dt'))
            ->andWhere(['>=', 'calls_raw.connect_time', $line->date_from])
            ->andWhere(['<=', 'calls_raw.connect_time', $line->date_to])
            ->andWhere(['service_trunk.trunk_id' => $service->trunk_id])
            ->andWhere(['service_trunk.client_account_id' => $line->bill->client_id])

            ->groupBy([
                'service_trunk.trunk_id',
                'service_trunk.client_account_id',
            ]);

        $margin = 0;

        foreach ($query->each(1000, CallsRaw::getDb()) as $marginRow) {
            if ($marginRow['client_account_id'] !== $line->bill->client_id) {
                continue;
            }
            $margin += $marginRow['orig'] - $marginRow['term'];
        }

        $reward->percentage_of_margin = $percentage * $margin / 100;
    }

}