<?php

namespace app\controllers\api;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\models\City;
use app\models\Country;
use app\models\Currency;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;

final class OpenController extends Controller
{

    const FREE_NUMBERS_PREVIEW_MODE = 4;

    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * @SWG\Get(
     *   tags={"Список свободных номеров"},
     *   path="/open/get-free-numbers",
     *   summary="Выбрать список свободных номеров по одному региону",
     *   operationId="Выбрать список свободных номеров по одному региону",
     *   @SWG\Parameter(name="region",type="integer",description="код региона",in="query"),
     *   @SWG\Parameter(name="currency",type="string",description="код валюты (ISO)",in="query"),
     *   @SWG\Response(
     *     response=200,
     *     description="Выбрать список свободных номеров",
     *     @SWG\Definition(
     *       type="object[]"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @param int $region
     * @param string $currency
     * @return array
     */
    public function actionGetFreeNumbers($region = null, $currency = Currency::RUB)
    {
        $numbers = (new FreeNumberFilter)
            ->getNumbers()
            ->setRegions([$region]);

        $response = [];
        foreach ($numbers->result(null) as $row) {
            $response[] = $numbers->formattedNumber($row, $currency);
        }

        return $response;
    }

    /**
     * @SWG\Get(
     *   tags={"Список свободных номеров"},
     *   path="/open/get-free-numbers-by-filter",
     *   summary="Выбрать список свободных номеров в зависимости от параметров",
     *   operationId="Выбрать список свободных номеров в зависимости от параметров",
     *   @SWG\Parameter(name="regions[0]",type="integer",description="код региона(ов)",in="query"),
     *   @SWG\Parameter(name="regions[1]",type="integer",description="код региона(ов)",in="query"),
     *   @SWG\Parameter(name="numberType",type="integer",description="тип номеров (const from NumberType)",in="query"),
     *   @SWG\Parameter(name="minCost",type="number",description="минимальная стоимость",in="query"),
     *   @SWG\Parameter(name="maxCost",type="number",description="максимальная стоимость",in="query"),
     *   @SWG\Parameter(name="beautyLvl",type="integer",description="уровень красоты",in="query"),
     *   @SWG\Parameter(name="like",type="string",description="Маска номера телефона. mySQL like syntax",in="query"),
     *   @SWG\Parameter(name="mask",type="string",description="Маска номера телефона. Допустимы [A-Z0-9*]",in="query"),
     *   @SWG\Parameter(name="offset",type="integer",description="смещение результатов поиска",in="query"),
     *   @SWG\Parameter(name="limit",type="integer",description="кол-во записей (default: 12, 'null' для получения всех)",in="query"),
     *   @SWG\Parameter(name="currency",type="string",description="код валюты (ISO)",in="query"),
     *   @SWG\Parameter(name="countryCode",type="integer",description="Код страны",in="query"),
     *   @SWG\Parameter(name="cities[0]",type="integer",description="ID города",in="query"),
     *   @SWG\Parameter(name="cities[1]",type="integer",description="ID города",in="query"),
     *   @SWG\Parameter(name="similar",type="string",description="Значение для подсчета схожести",in="query"),
     *   @SWG\Response(
     *     response=200,
     *     description="Выбрать список свободных номеров",
     *     @SWG\Definition(
     *       type="object[]"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @param array $regions
     * @param null $numberType
     * @param null $minCost
     * @param null $maxCost
     * @param array $beautyLvl
     * @param null $like
     * @param null $mask
     * @param int $offset
     * @param int $limit
     * @param string $currency
     * @param int $countryCode
     * @param array $cities
     * @param null $similar
     * @return array
     */
    public function actionGetFreeNumbersByFilter(
        array $regions = [],
        $numberType = null,
        $minCost = null,
        $maxCost = null,
        array $beautyLvl = [],
        $like = null,
        $mask = null,
        $offset = 0,
        $limit = FreeNumberFilter::FREE_NUMBERS_LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $similar = null
    ) {
        $numbers = (new FreeNumberFilter)
            ->setRegions($regions)
            ->setCountry($countryCode)
            ->setCities($cities)
            ->setMinCost($minCost)
            ->setMaxCost($maxCost)
            ->setBeautyLvl($beautyLvl)
            ->setNumberLike($like)
            ->setNumberMask($mask)
            ->setSimilar($similar);

        if ((int)$offset) {
            $numbers->setOffset((int)$offset);
        }

        if ((int)$numberType) {
            $numbers->setType((int)$numberType);
        }

        $response = [];

        foreach ($numbers->orderByPrice()->result($limit) as $row) {
            $response[] = $numbers->formattedNumber($row, $currency);
        }

        return $response;
    }

    /**
     * @SWG\Definition(definition="number", type="object",
     *   @SWG\Property(property="number", type="string", description="Номер"),
     *   @SWG\Property(property="beauty_level", type="integer", description="Уровень красоты"),
     *   @SWG\Property(property="price", type="integer", description="Цена"),
     *   @SWG\Property(property="currency", type="string", description="Код валюты (ISO)"),
     *   @SWG\Property(property="originPrice", type="integer", description="Цена указанная для Did group"),
     *   @SWG\Property(property="originCurrency", type="string", description="Код валюты (ISO) указанный для Did group"),
     *   @SWG\Property(property="region", type="integer", description="ID региона"),
     *   @SWG\Property(property="city_id", type="integer", description="ID города"),
     *   @SWG\Property(property="site_publish", type="boolean", description="Публиковать на сайте или нет"),
     *   @SWG\Property(property="did_group_id", type="integer", description="ID DID группы"),
     *   @SWG\Property(property="number_type", type="integer", description="Тип номера (внутренний, внешний etc) см. models\NumberType"),
     * ),
     * @SWG\Definition(definition="numbers", type="object",
     *   @SWG\Property(property="beauty_level", type="string", description="Наименование уровня красоты"),
     *   @SWG\Property(property="numbers", type="array", description="Массив свободных номеров в стране/городе/уровне красоты",
     *     @SWG\Items(
     *       ref="#/definitions/number"
     *     )
     *   ),
     * ),
     * @SWG\Definition(definition="city", type="object",
     *   @SWG\Property(property="city_id", type="integer", description="Идентификатор города"),
     *   @SWG\Property(property="city_name", type="string", description="Наименование города"),
     *   @SWG\Property(property="numbers", type="array", description="Массив свободных номеров",
     *     @SWG\Items(
     *       ref="#/definitions/numbers"
     *     )
     *   )
     * ),
     * @SWG\Definition(definition="country", type="object",
     *   @SWG\Property(property="country_id", type="integer", description="Идентификатор страны"),
     *   @SWG\Property(property="country_name", type="string", description="Наименование страны"),
     *   @SWG\Property(property="cities", type="array", description="Массив городов входящих в состав страны",
     *     @SWG\Items(
     *       ref="#/definitions/city"
     *     )
     *   )
     * ),
     * @SWG\Get(
     *   tags={"Список свободных номеров"},
     *   path="/open/get-free-numbers-preview",
     *   summary="Получение свободных номеров по странам/городам/уровням красоты",
     *   operationId="Получение свободных номеров по странам/городам/уровням красоты",
     *   @SWG\Response(
     *     response=200,
     *     description="Свободные номера по странам/городам/уровням красоты",
     *     @SWG\Definition(
     *       ref="#/definitions/country"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @param string $currency - Currency constants (Currency::RUB, Currency::HUF etc)
     * @return array
     *
     * Структура данных:
     * [
     *   [
     *      county_id,
     *      county_name,
     *      cities => [
     *          city_id,
     *          city_name,
     *          numbers => [
     *              beauty_level,
     *              numbers => [
     *                  number,
     *                  beauty_level,
     *                  price,
     *                  currency,
     *                  originPrice,
     *                  originCurrency,
     *                  region,
     *                  city_id,
     *                  did_group_id,
     *                  number_type,
     *                  site_publish,
     *              ]
     *          ]
     *      ]
     *  ]
     */
    public function actionGetFreeNumbersPreview($currency = Currency::RUB)
    {
        $countries = Country::find()
            ->where(['in_use' => 1])
            ->orderBy(['code' => SORT_DESC])
            ->asArray()
            ->all();

        $cities = City::find()
            ->where(['IN', 'country_id', (array)ArrayHelper::getColumn($countries, 'code')])
            ->orderBy([
                'order' => SORT_ASC,
                'name' => SORT_ASC,
            ])
            ->asArray()
            ->all();

        $beautyLvls = [
            DidGroup::BEAUTY_LEVEL_STANDART,
            DidGroup::BEAUTY_LEVEL_BRONZE,
            DidGroup::BEAUTY_LEVEL_SILVER,
            DidGroup::BEAUTY_LEVEL_GOLD,
            DidGroup::BEAUTY_LEVEL_PLATINUM,
        ];

        $result = [];
        foreach ($countries as $country) {
            $countryCities = array_filter($cities, function ($row) use ($country) {
                return $row['country_id'] === $country['code'];
            });

            if (!(new FreeNumberFilter)->getNumbers()->setCities(ArrayHelper::getColumn($countryCities, 'id'))->count()) {
                continue;
            }

            $countryRow = [
                'country_id' => $country['code'],
                'country' => $country['name'],
                'cities' => [],
            ];

            foreach ($countryCities as $city) {
                if (!(new FreeNumberFilter)->getNumbers()->setCity($city['id'])->count()) {
                    continue;
                }

                $cityRow = [
                    'city_id' => $city['id'],
                    'city' => $city['name'],
                    'numbers' => [],
                ];

                foreach ($beautyLvls as $beautyLvl) {
                    $numbersFilter = new FreeNumberFilter;
                    $numbers = $numbersFilter
                        ->setCity($city['id'])
                        ->setBeautyLvl([$beautyLvl])
                        ->result(self::FREE_NUMBERS_PREVIEW_MODE);

                    $cityRow['numbers'][] = [
                        'beauty_level' => $beautyLvl,
                        'numbers' => $numbersFilter->formattedNumbers($numbers, $currency),
                    ];
                }

                $countryRow['cities'][] = $cityRow;
            }

            $result[] = $countryRow;
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition="did_group", type="object",
     *   @SWG\Property(property="id", type="integer", description="Идентификатор группы"),
     *   @SWG\Property(property="name", type="string", description="Наименование группы"),
     *   @SWG\Property(property="country_code", type="integer", description="Идентификатор страны"),
     *   @SWG\Property(property="city_id", type="integer", description="Идентификатор города"),
     *   @SWG\Property(property="beauty_level", type="integer", description="Степень красоты"),
     *   @SWG\Property(property="number_type_id", type="integer", description="Тип номеров")
     * ),
     * @SWG\Get(
     *   tags={"Список DID групп"},
     *   path="/open/did-groups",
     *   summary="Получение DID групп",
     *   operationId="Получение DID групп",
     *   @SWG\Parameter(name="id[0]",type="integer",description="идентификатор(ы) DID групп",in="query",),
     *   @SWG\Parameter(name="id[1]",type="integer",description="идентификатор(ы) DID групп",in="query"),
     *   @SWG\Parameter(name="id[2]",type="integer",description="идентификатор(ы) DID групп",in="query"),
     *   @SWG\Parameter(name="beautyLvl[0]",type="integer",description="уровень(ни) красоты",in="query"),
     *   @SWG\Parameter(name="beautyLvl[1]",type="integer",description="уровень(ни) красоты",in="query"),
     *   @SWG\Parameter(name="beautyLvl[2]",type="integer",description="уровень(ни) красоты",in="query"),
     *   @SWG\Response(
     *     response=200,
     *     description="Список DID групп",
     *     @SWG\Definition(
     *       ref="#/definitions/did_group"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @param int[] $id
     * @param int[] $beautyLvl
     * @return DidGroup[]
     */
    public function actionDidGroups(array $id = [], array $beautyLvl = [])
    {
        $result = DidGroup::find();

        if (count($id)) {
            $result->andWhere(['IN', 'id', $id]);
        }

        if (count($beautyLvl)) {
            $result->andWhere(['IN', 'beauty_level', $beautyLvl]);
        }

        return $result->all();
    }
}