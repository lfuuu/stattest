<?php

namespace app\controllers\api;

use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\exceptions\web\BadRequestHttpException;
use app\models\NumberType;
use app\models\filter\FreeNumberFilter;

final class OpenController extends Controller
{

    const FREE_NUMBERS_PREVIEW_MODE = 4;

    public $enableCsrfValidation = false;

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
    public function actionGetFreeNumbers($region = null)
    {
        $numbers =
            (new FreeNumberFilter)
                ->getNumbers()
                ->setRegions([$region]);

        $response = [];
        foreach ($numbers->each()->result(null) as $r) {
            $response[] = [
                'number' => $r->number,
                'beauty' => $r->beauty_level,
                'price' => $r->price,
                'region' => $r->region,
            ];
        }
        return $response;
    }

    /**
     * @SWG\Get(
     *   tags={"Список свободных номеров"},
     *   path="/open/get-free-numbers-by-filter",
     *   summary="Выбрать список свободных номеров в зависимости от параметров",
     *   operationId="Выбрать список свободных номеров в зависимости от параметров",
     *   @SWG\Parameter(name="regions[]",type="integer[]",description="код региона(ов)",in="query"),
     *   @SWG\Parameter(name="regions[]",type="integer[]",description="код региона(ов)",in="query"),
     *   @SWG\Parameter(name="numberType",type="integer",description="тип номеров (const from NumberType)",in="query"),
     *   @SWG\Parameter(name="minCost",type="float",description="минимальная стоимость",in="query"),
     *   @SWG\Parameter(name="maxCost",type="float",description="максимальная стоимость",in="query"),
     *   @SWG\Parameter(name="beautyLvl",type="integer",description="уровень красоты",in="query"),
     *   @SWG\Parameter(name="like",type="string",description="выражение для поиска вхождения",in="query"),
     *   @SWG\Parameter(name="offset",type="integer",description="смещение результатов поиска",in="query"),
     *   @SWG\Parameter(name="limit",type="integer",description="кол-во записей (default: 12, 'null' для получения всех)",in="query"),
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
    public function actionGetFreeNumbersByFilter(
        array $regions = [],
        $numberType = null,
        $minCost = null,
        $maxCost = null,
        array $beautyLvl = [],
        $like = null,
        $offset = 0,
        $limit = FreeNumberFilter::FREE_NUMBERS_LIMIT
    ) {
        $numbers =
            (new FreeNumberFilter)
                ->setRegions($regions)
                ->setMinCost($minCost)
                ->setMaxCost($maxCost)
                ->setBeautyLvl($beautyLvl);
        if (!is_null($like)) {
            if (!preg_match('#^%?\d{3,}%$#', $like)) {
                throw new BadRequestHttpException('Bad format for mask search');
            }
            $numbers->setNumberMask($like);
        }
        if ((int)$offset) {
            $numbers->setOffset((int)$offset);
        }
        if ((int)$numberType) {
            $numbers->setType((int)$numberType);
        }

        $response = [];

        foreach ($numbers->orderByPrice()->each()->result($limit) as $row) {
            $response[] = [
                'number' => $row->number,
                'beauty' => $row->beauty_level,
                'price' => $row->price,
                'region' => $row->region,
            ];
        }

        return $response;
    }

    /**
     * @SWG\Definition(
     *   definition="number",
     *   type="object",
     *   @SWG\Property(
     *     property="number",
     *     type="string",
     *     description="Номер"
     *   ),
     *   @SWG\Property(
     *     property="status",
     *     type="string",
     *     description="Статус номера (instock:Свободен etc)"
     *   ),
     *   @SWG\Property(
     *     property="reserve_from",
     *     type="datetime",
     *     description="Номер в резерве с datetime"
     *   ),
     *   @SWG\Property(
     *     property="reserve_till",
     *     type="datetime",
     *     description="Номер в резерве до datetime"
     *   ),
     *   @SWG\Property(
     *     property="hold_from",
     *     type="datetime",
     *     description="Номер в отстойнике с datetime"
     *   ),
     *   @SWG\Property(
     *     property="hold_to",
     *     type="datetime",
     *     description="Номер в отстойнике до datetime"
     *   ),
     *   @SWG\Property(
     *     property="beauty_level",
     *     type="integer",
     *     description="Уровень красоты"
     *   ),
     *   @SWG\Property(
     *     property="price",
     *     type="integer",
     *     description="Цена"
     *   ),
     *   @SWG\Property(
     *     property="region",
     *     type="integer",
     *     description="ID региона"
     *   ),
     *   @SWG\Property(
     *     property="city_id",
     *     type="integer",
     *     description="ID города"
     *   ),
     *   @SWG\Property(
     *     property="client_id",
     *     type="integer",
     *     description="ID Л/С / NULL, если номер принадлежит клиенту"
     *   ),
     *   @SWG\Property(
     *     property="usage_id",
     *     type="integer",
     *     description="ID услуги / NULL, если номер принадлежит клиенту"
     *   ),
     *   @SWG\Property(
     *     property="reserved_free_date",
     *     type="datetime",
     *     description="Резерв будет снят принудительно datetime"
     *   ),
     *   @SWG\Property(
     *     property="used_until_date",
     *     type="datetime",
     *     description="Номер используется до datetime"
     *   ),
     *   @SWG\Property(
     *     property="edit_user_id",
     *     type="integer",
     *     description="ID пользователя, редактировавшего номер последним"
     *   ),
     *   @SWG\Property(
     *     property="site_publish",
     *     type="boolean",
     *     description="Публиковать на сайте или нет"
     *   ),
     *   @SWG\Property(
     *     property="did_group_id",
     *     type="integer",
     *     description="ID DID группы"
     *   ),
     *   @SWG\Property(
     *     property="number_tech",
     *     type="integer",
     *     description="-"
     *   ),
     *   @SWG\Property(
     *     property="operator_account_id",
     *     type="integer",
     *     description="ID оператора, если номер используется оператором"
     *   ),
     *   @SWG\Property(
     *     property="country_code",
     *     type="integer",
     *     description="ID страны"
     *   ),
     *   @SWG\Property(
     *     property="ndc",
     *     type="integer",
     *     description="Признак номера 7800"
     *   ),
     *   @SWG\Property(
     *     property="number_subscriber",
     *     type="string",
     *     description="-"
     *   ),
     *   @SWG\Property(
     *     property="number_type",
     *     type="integer",
     *     description="Тип номера (внутренний, внешний etc) см. models\NumberType"
     *   ),
     *   @SWG\Property(
     *     property="date_start",
     *     type="date",
     *     description="Номер используется с date"
     *   ),
     *   @SWG\Property(
     *     property="date_end",
     *     type="date",
     *     description="Номер используется до date"
     *   ),
     *   @SWG\Property(
     *     property="number_cut",
     *     type="string",
     *     description="Часть номера, используемая для выборки случайного номера"
     *   ),
     * ),
     * @SWG\Definition(
     *   definition="numbers",
     *   type="object",
     *   @SWG\Property(
     *     property="beauty_level",
     *     type="string",
     *     description="Наименование уровня красоты"
     *   ),
     *   @SWG\Property(
     *     property="numbers",
     *     type="array",
     *     description="Массив свободных номеров в стране/городе/уровне красоты",
     *     @SWG\Items(
     *       ref="#/definitions/number"
     *     )
     *   ),
     * ),
     * @SWG\Definition(
     *   definition="city",
     *   type="object",
     *   @SWG\Property(
     *     property="city_id",
     *     type="integer",
     *     description="Идентификатор города"
     *   ),
     *   @SWG\Property(
     *     property="city_name",
     *     type="string",
     *     description="Наименование города"
     *   ),
     *   @SWG\Property(
     *     property="numbers",
     *     type="array",
     *     description="Массив свободных номеров",
     *     @SWG\Items(
     *       ref="#/definitions/numbers"
     *     )
     *   )
     * ),
     * @SWG\Definition(
     *   definition="country",
     *   type="object",
     *   @SWG\Property(
     *     property="country_id",
     *     type="integer",
     *     description="Идентификатор страны"
     *   ),
     *   @SWG\Property(
     *     property="country_name",
     *     type="string",
     *     description="Наименование страны"
     *   ),
     *   @SWG\Property(
     *     property="cities",
     *     type="array",
     *     description="Массив городов входящих в состав страны",
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
     *                 number,
     *                 status,
     *                 reserve_from,
     *                 reserve_till,
     *                 hold_from,
     *                 hold_to,
     *                 beauty_level,
     *                 price,
     *                 region,
     *                 client_id,
     *                 usage_id,
     *                 reserved_free_date,
     *                 used_until_date,
     *                 edit_user_id,
     *                 site_publish,
     *                 city_id,
     *                 did_group_id,
     *                 number_tech,
     *                 operator_account_id,
     *                 country_code,
     *                 ndc,
     *                 number_subscriber,
     *                 number_type,
     *                 date_start,
     *                 date_end,
     *                 number_cut
     *              ]
     *          ]
     *      ]
     *  ]
     */
    public function actionGetFreeNumbersPreview()
    {
        $countries =
            Country::find()
                ->where(['in_use' => 1])
                ->orderBy(['code' => SORT_DESC])
                ->asArray()
                ->all();

        $cities =
            City::find()
                ->where(['IN', 'country_id', (array)ArrayHelper::getColumn($countries, 'code')])
                ->orderBy('name')
                ->asArray()
                ->all();

        $beautyLvls = DidGroup::$beautyLevelNames;

        $result = [];
        foreach ($countries as $country) {
            $countryCities = array_filter($cities, function ($row) use ($country) {
                return $row['country_id'] === $country['code'];
            });

            $countryRow = [
                'country_id' => $country['code'],
                'country' => $country['name'],
                'cities' => [],
            ];

            foreach ($countryCities as $city) {
                $cityRow = [
                    'city_id' => $city['id'],
                    'city' => $city['name'],
                    'numbers' => [],
                ];

                foreach ($beautyLvls as $beautyLvl => $beautyTitle) {
                    $numbers = (new FreeNumberFilter)
                        ->setCity($city['id'])
                        ->setBeautyLvl([$beautyLvl])
                        ->asArray()
                        ->result(self::FREE_NUMBERS_PREVIEW_MODE);

                    $cityRow['numbers'][] = [
                        'beauty_level' => $beautyTitle,
                        'numbers' => $numbers,

                    ];
                }

                $countryRow['cities'][] = $cityRow;
            }

            $result[] = $countryRow;
        }

        return $result;
    }

}
