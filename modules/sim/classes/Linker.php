<?php


namespace app\modules\sim\classes;


use app\classes\Singleton;
use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
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
            ->andWhere(new Expression('i.imsi::text like  \'250%\''));

        $cards = [];
        /** @var Imsi $imsi */
        foreach ($imsiQuery->each() as $imsi) {
            $cards[$imsi->card->iccid] = $imsi->card->iccid;
        }

        $accountTariffQuery = \app\modules\uu\models\AccountTariff::find()
            ->where([
                'client_account_id' => $accountId,
                'service_type_id' => \app\modules\uu\models\ServiceType::ID_VOIP
            ])->andWhere(['NOT', ['tariff_period_id' => null]])
            ->with('number');

        $accountTariffs = [];
        foreach ($accountTariffQuery->each() as $accountTariff) {
            if (!$accountTariff->number->imsi) {
                $accountTariffs[$accountTariff->id] = $accountTariff->voip_number;
            }
        }

        return [$cards, $accountTariffs];
    }
}