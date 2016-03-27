<?php

use yii\helpers\ArrayHelper;
use app\models\usages\UsageInterface;
use app\models\UsageWelltime;
use app\models\UsageVirtpbx;
use app\models\TariffVirtpbx;
use app\models\TariffExtra;

class m160327_101649_vats_to_welltime extends \app\classes\Migration
{
    public function up()
    {
        $usages = [
            2992,2683,2685,2888,2884,2866,2821,2934,2804,3454,844,3014,2730,2860,2742,2891,2686,3681,3065,2714,2940,
            2939,2863,2736,2688,2678,2675,2674,2682,2703,2709,2716,2858,2766,2721,2735,2731,2746,2743,2750,2761,2767,
            2811,2814,2960,2760,2780,2765,2825,2772,2969,2769,2779,2787,2782,2813,2903,2827,2885,2832,2831,2840,847,
            842,846,2867,2865,2868,3013,2882,2881,3011,3275,2893,2910,2959,3238,2911,2912,3039,2913,2914,2917,2919,
            2945,2923,2925,2924,2928,2961,2931,3049,2937,2938,2943,2948,2953,2967,3128,2968,2987,2984,2985,2980,3007,
            2991,2999,3001,3000,2997,2993,3004,3003,3025,3038,3051,3043,3044,3053,3145,3542,3319,3773,3874,2673,3190,
            2864,2929,2822,2922,2812,2994,2966,2845,2859,2852,2807,2996,3140,2785,3382,3022
        ];

        $usages =
            UsageVirtpbx::find()
                ->where(['in', 'id', $usages])
                ->andWhere(['!=', 'tarif_id', 0])
                ->all();

        $tariffsIds = array_filter(
            array_values(
                array_unique(ArrayHelper::getColumn((array) $usages, 'tarif_id'))
            ),
            function($value) {
                return $value;
            }
        );

        $tariffs =
            TariffVirtpbx::find()
                ->where(['in', 'id', $tariffsIds]);

        unset($tariffsIds);

        foreach ($tariffs->each() as $tariff) {
            $extraTariff = TariffExtra::find()
                ->where([
                    'description' => $tariff->description,
                    'price' => $tariff->price,
                    'price_include_vat' => $tariff->price_include_vat,
                    'currency' => $tariff->currency,
                    'status' => $tariff->status,
                    'period' => $tariff->period,
                ])
                ->one();

            if (is_null($extraTariff)) {
                $extraTariff = new TariffExtra;

                $extraTariff->code = 'welltime';
                $extraTariff->description = $tariff->description;
                $extraTariff->price = $tariff->price;
                $extraTariff->price_include_vat = $tariff->price_include_vat;
                $extraTariff->currency = $tariff->currency;
                $extraTariff->status = $tariff->status;
                $extraTariff->period = $tariff->period;

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $extraTariff->save();
                    $transaction->commit();
                }
                catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }

            if (!is_null($extraTariff)) {
                $tariffsIds[$tariff->id] = $extraTariff;
            }
        }

        $lastDayOfThisMonth = new DateTime('last day of this month');

        foreach ($usages as $usage) {
            $existingUsage =
                UsageWelltime::find()
                    ->where(['tarif_id' => $tariffsIds[$usage->tarif_id]->id])
                    ->andWhere('actual_from > CAST(:date AS DATE)', [':date' => $lastDayOfThisMonth->format('Y-m-d')])
                    ->client($usage->client)
                    ->one();

            if (!is_null($existingUsage)) {
                continue;
            }

            $router = 'pbx.welltime.ru';
            $ip = '';

            if (preg_match('#^\d\s([a-zA-Z\.]+),#', $usage->comment, $match)) {
                $matchRouter = mb_strtolower($match[1], 'UTF-8');
                if (strpos($router, $matchRouter) !== 0) {
                    $router = $matchRouter;
                }
            }
            if (preg_match('#\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b#', $usage->comment, $match)) {
                $ip = $match[0];
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $usage->actual_to = $lastDayOfThisMonth->format('Y-m-d');
                $usage->save();

                $usageWelltime = new UsageWelltime;
                $usageWelltime->client = $usage->client;
                $usageWelltime->actual_from = $lastDayOfThisMonth->modify('+1 day')->format('Y-m-d');
                $usageWelltime->actual_to = UsageInterface::MAX_POSSIBLE_DATE;
                $usageWelltime->comment = $usage->comment;
                $usageWelltime->tarif_id = $tariffsIds[$usage->tarif_id]->id;
                $usageWelltime->router = $router;
                $usageWelltime->ip = $ip;
                $usageWelltime->save();

                $transaction->commit();
            }
            catch (\Exception $e) {
                $transaction->rollBack();
            }
        }
    }

    public function down()
    {
    }
}