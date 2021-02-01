<?php

namespace app\modules\sim\classes;

use app\classes\Singleton;
use app\models\Number;
use app\models\Region;
use app\modules\nnp\models\NdcType;
use app\modules\sim\models\Imsi;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\db\Expression;

class Linker extends Singleton
{
    public function getDataByAccountId($accountId)
    {
        $imsiQuery = Imsi::find()
            ->alias('i')
            ->joinWith('card c', true, 'INNER JOIN')
            ->where([
                'client_account_id' => $accountId,
                'i.msisdn' => null,
            ])
            ->andWhere(new Expression('i.imsi::text like \'250%\''))
            ->with('card');

        $accountTariffQuery = AccountTariff::find()
            ->alias('at')
            ->innerJoin(['v' => Number::tableName()], 'v.number = at.voip_number')
            ->where([
                'at.client_account_id' => $accountId,
                'at.service_type_id' => ServiceType::ID_VOIP
            ])
            ->andWhere(['NOT', ['at.tariff_period_id' => null]])
            ->andWhere(['v.ndc_type_id' => NdcType::ID_MOBILE])
            ->with('number');

        // collect region ids
        $regionIds = [];
        /** @var Imsi $imsi */
        foreach ($imsiQuery->each() as $imsi) {
            $regionId = $imsi->card->region_id;
            $regionIds[$regionId] = $regionId;
        }
        /** @var AccountTariff $accountTariff */
        foreach ($accountTariffQuery->each() as $accountTariff) {
            if (!$accountTariff->number->imsi) {
                $regionId = $accountTariff->number->region;
                $regionIds[$regionId] = $regionId;
            }
        }

        // get regions
        $regions = Region::find()
            ->andWhere(['id' => $regionId])
            ->indexBy('id')
            ->all();

        // entities
        $cards = [];
        /** @var Imsi $imsi */
        foreach ($imsiQuery->each() as $imsi) {
            $regionId = $imsi->card->region_id;
            $regionName = '';
            if ($region = $regions[$regionId] ?? null) {
                $regionName = $region->name;
            }

            $cards[$imsi->card->iccid] = $imsi->card->iccid . ($regionName ? ' / ' . $regionName : '');
        }

        $accountTariffs = [];
        /** @var AccountTariff $accountTariff */
        foreach ($accountTariffQuery->each() as $accountTariff) {
            if (!$accountTariff->number->imsi) {
                $regionId = $accountTariff->number->region;
                $regionName = '';
                if ($region = $regions[$regionId] ?? null) {
                    $regionName = $region->name;
                }

                $accountTariffs[$accountTariff->id] = $accountTariff->voip_number . ($regionName ? ' / ' . $regionName : '');
            }
        }

        return [$cards, $accountTariffs];
    }
}