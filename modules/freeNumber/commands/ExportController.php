<?php

namespace app\modules\freeNumber\commands;

use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\modules\nnp\models\NdcType;
use yii\console\Controller;


class ExportController extends Controller
{
    /**
     * Экспортировать свободные номера
     */
    public function actionNumber()
    {
        $query = (new FreeNumberFilter)
            ->setIsService(false)
            ->setIsShowInLk(City::IS_SHOW_IN_LK_API_ONLY)
            ->getQuery();

        echo implode("\t", ['number', 'beauty_level', 'country_code', 'city_id', 'ndc_type_id']) . PHP_EOL;

        /** @var \app\models\Number $number */
        foreach ($query->each() as $number) {
            echo implode("\t", [$number->number, $number->beauty_level, $number->country_code, $number->city_id, $number->ndc_type_id]) . PHP_EOL;
        }
    }

    /**
     * Экспортировать города
     */
    public function actionCity()
    {
        $query = City::find()
            ->where(['>=', 'is_show_in_lk', City::IS_SHOW_IN_LK_API_ONLY]); // нумерация идет последовательно. Поэтому проще ">=" минимального, а не "in" все возможные

        echo implode("\t", ['id', 'name', 'country_code', 'voip_number_format', 'postfix_length']) . PHP_EOL;

        /** @var City $city */
        foreach ($query->each() as $city) {
            echo implode("\t", [$city->id, $city->name, $city->country_id, $city->voip_number_format, $city->postfix_length]) . PHP_EOL;
        }
    }

    /**
     * Экспортировать страны
     */
    public function actionCountry()
    {
        $query = Country::find()
            ->where(['is_show_in_lk' => 1, 'in_use' => 1]);

        echo implode("\t", ['code', 'alpha_3', 'name', 'name_rus']) . PHP_EOL;

        /** @var Country $country */
        foreach ($query->each() as $country) {
            echo implode("\t", [$country->code, $country->alpha_3, $country->name, $country->name_rus]) . PHP_EOL;
        }
    }

    /**
     * Экспортировать типы NDC
     */
    public function actionNdcType()
    {
        $query = NdcType::find();

        echo implode("\t", ['id', 'name']) . PHP_EOL;

        /** @var NdcType $ndcType */
        foreach ($query->each() as $ndcType) {
            echo implode("\t", [$ndcType->id, $ndcType->name]) . PHP_EOL;
        }
    }

    /**
     * Экспортировать уровни красивость
     */
    public function actionBeautyLevel()
    {
        echo implode("\t", ['id', 'name']) . PHP_EOL;

        foreach (DidGroup::$beautyLevelNames as $id => $name) {
            echo implode("\t", [$id, $name]) . PHP_EOL;
        }
    }
}
