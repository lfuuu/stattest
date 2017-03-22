<?php

namespace app\controllers\api;

use app\models\Currency;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use Yii;
use yii\web\Controller;

final class OpenController extends Controller
{

    const FREE_NUMBERS_PREVIEW_MODE = 4;

    public $enableCsrfValidation = false;

    /**
     * Инициализация
     */
    public function init()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * @SWG\Get(tags={"Список свободных номеров"}, path="/open/get-free-numbers", summary="Выбрать список свободных номеров по одному региону", operationId="Выбрать список свободных номеров по одному региону",
     *   @SWG\Parameter(name="region", type="integer", description="код региона", in="query", default=""),
     *   @SWG\Parameter(name="currency", type="string", description="код валюты (ISO)", in="query", default=""),
     *   @SWG\Response(response=200, description="Выбрать список свободных номеров", @SWG\Items(ref = "#/definitions/freeNumberRecord")),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     *
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
     * @SWG\Definition(definition="freeNumberRecords", type="object",
     *   @SWG\Property(property="total", type="int", description="Всего номеров, удовлетворяющих условиям запроса без limit/offset"),
     *   @SWG\Property(property="numbers", type="array", description="Номер", @SWG\Items(ref = "#/definitions/freeNumberRecord")),
     * ),
     *
     * @SWG\Definition(definition="freeNumberRecord", type="object",
     *   @SWG\Property(property="number", type="string", description="Номер"),
     *   @SWG\Property(property="beauty_level", type="integer", description="Уровень красоты"),
     *   @SWG\Property(property="price", type="integer", description="Цена"),
     *   @SWG\Property(property="currency", type="string", description="Код валюты (ISO)"),
     *   @SWG\Property(property="origin_price", type="integer", description="Исходная цена"),
     *   @SWG\Property(property="origin_currency", type="string", description="Исходный код валюты (ISO)"),
     *   @SWG\Property(property="region", type="integer", description="ID региона"),
     *   @SWG\Property(property="city_id", type="integer", description="ID города"),
     *   @SWG\Property(property="did_group_id", type="integer", description="ID DID-группы"),
     *   @SWG\Property(property="number_type", type="integer", description="ID типа номера"),
     *   @SWG\Property(property="ndc", type="integer", description="NDC")
     * ),
     *
     * @SWG\Get(tags={"Список свободных номеров"}, path="/open/get-free-numbers-by-filter", summary="Выбрать список свободных номеров", operationId="Выбрать список свободных номеров",
     *   @SWG\Parameter(name="regions[0]", type="integer", description="Код региона(ов)", in="query", default=""),
     *   @SWG\Parameter(name="regions[1]", type="integer", description="Код региона(ов)", in="query", default=""),
     *   @SWG\Parameter(name="numberType", type="integer", description="Тип номеров (const from NumberType)", in="query", default=""),
     *   @SWG\Parameter(name="minCost", type="number", description="Минимальная цена", in="query", default=""),
     *   @SWG\Parameter(name="maxCost", type="number", description="Максимальная цена", in="query", default=""),
     *   @SWG\Parameter(name="beautyLvl", type="integer", description="Уровень красоты", in="query", default=""),
     *   @SWG\Parameter(name="like", type="string", description="Маска номера телефона. Синтахис: '.' - один символ, '*' - любое кол-во символов", in="query", default=""),
     *   @SWG\Parameter(name="mask", type="string", description="Маска номера телефона. Допустимы [A-Z0-9*]", in="query", default=""),
     *   @SWG\Parameter(name="offset", type="integer", description="Смещение результатов поиска", in="query", default=""),
     *   @SWG\Parameter(name="limit", type="integer", description="Кол-во записей (default: 12, 'null' для получения всех)", in="query", default=""),
     *   @SWG\Parameter(name="currency", type="string", description="Код валюты (ISO)", in="query", default=""),
     *   @SWG\Parameter(name="countryCode", type="integer", description="Код страны", in="query", default=""),
     *   @SWG\Parameter(name="cities[0]", type="integer", description="ID города", in="query", default=""),
     *   @SWG\Parameter(name="cities[1]", type="integer", description="ID города", in="query", default=""),
     *   @SWG\Parameter(name="similar", type="string", description="Значение для подсчета схожести", in="query", default=""),
     *   @SWG\Parameter(name="ndc", type="integer", description="NDC", in="query", default=""),
     *   @SWG\Parameter(name="excludeNdcs[0]", type="integer", description="Кроме NDC", in="query", default=""),
     *   @SWG\Parameter(name="excludeNdcs[1]", type="integer", description="Кроме NDC", in="query", default=""),
     *
     *   @SWG\Response(response=200, description="Выбрать список свободных номеров", @SWG\Definition(ref = "#/definitions/freeNumberRecords")),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     *
     * @param array $regions
     * @param int $numberType
     * @param float $minCost
     * @param float $maxCost
     * @param int $beautyLvl
     * @param string $like
     * @param string $mask
     * @param int $offset
     * @param int $limit
     * @param string $currency
     * @param int $countryCode
     * @param array $cities
     * @param string $similar
     * @param int $ndc
     * @param int|int[] $excludeNdcs
     * @return array
     */
    public function actionGetFreeNumbersByFilter(
        array $regions = [],
        $numberType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $mask = null,
        $offset = 0,
        $limit = FreeNumberFilter::FREE_NUMBERS_LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $similar = null,
        $ndc = null,
        array $excludeNdcs = []
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
            ->setSimilar($similar)
            ->setNdc($ndc)
            ->setExcludeNdcs($excludeNdcs)
            ->orderBy(['number' => SORT_ASC]);

        if ((int)$offset) {
            $numbers->setOffset((int)$offset);
        }

        if ((int)$numberType) {
            $numbers->setType((int)$numberType);
        }

        $responseNumbers = [];

        foreach ($numbers->result($limit) as $freeNumberFilter) {
            $responseNumbers[] = $numbers->formattedNumber($freeNumberFilter, $currency);
        }

        return [
            'total' => $numbers->count(),
            'numbers' => $responseNumbers,
        ];
    }

    /**
     * @SWG\Definition(definition="freeNumberNdcRecord", type="object",
     *   @SWG\Property(property="ndc", type="integer", description="NDC"),
     *   @SWG\Property(property="numbers", type="array", description="Номера", @SWG\Items(ref = "#/definitions/freeNumberRecord"))
     * ),
     *
     * @SWG\Get(tags={"Список свободных номеров"}, path="/open/get-free-numbers-by-ndc", summary="Выбрать список свободных номеров и сгруппировать по NDC", operationId="Выбрать список свободных номеров и сгруппировать по NDC",
     *   @SWG\Parameter(name="regions[0]", type="integer", description="Код региона(ов)", in="query", default=""),
     *   @SWG\Parameter(name="regions[1]", type="integer", description="Код региона(ов)", in="query", default=""),
     *   @SWG\Parameter(name="numberType", type="integer", description="Тип номеров (const from NumberType)", in="query", default=""),
     *   @SWG\Parameter(name="minCost", type="number", description="Минимальная цена", in="query", default=""),
     *   @SWG\Parameter(name="maxCost", type="number", description="Максимальная цена", in="query", default=""),
     *   @SWG\Parameter(name="beautyLvl", type="integer", description="Уровень красоты", in="query", default=""),
     *   @SWG\Parameter(name="like", type="string", description="Маска номера телефона. Синтахис: '.' - один символ, '*' - любое кол-во символов", in="query", default=""),
     *   @SWG\Parameter(name="mask", type="string", description="Маска номера телефона. Допустимы [A-Z0-9*]", in="query", default=""),
     *   @SWG\Parameter(name="offset", type="integer", description="Смещение результатов поиска", in="query", default=""),
     *   @SWG\Parameter(name="limit", type="integer", description="Кол-во записей (default: 12, 'null' для получения всех)", in="query", default=""),
     *   @SWG\Parameter(name="currency", type="string", description="Код валюты (ISO)", in="query", default=""),
     *   @SWG\Parameter(name="countryCode", type="integer", description="Код страны", in="query", default=""),
     *   @SWG\Parameter(name="cities[0]", type="integer", description="ID города", in="query", default=""),
     *   @SWG\Parameter(name="cities[1]", type="integer", description="ID города", in="query", default=""),
     *   @SWG\Parameter(name="similar", type="string", description="Значение для подсчета схожести", in="query", default=""),
     *   @SWG\Parameter(name="ndc", type="integer", description="NDC", in="query", default=""),
     *   @SWG\Response(response=200, description="Выбрать список свободных номеров  и сгруппировать по NDC", @SWG\Items(ref = "#/definitions/freeNumberNdcRecord")),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     *
     * @param array $regions
     * @param int $numberType
     * @param float $minCost
     * @param float $maxCost
     * @param int $beautyLvl
     * @param string $like
     * @param string $mask
     * @param int $offset
     * @param int $limit
     * @param string $currency
     * @param int $countryCode
     * @param array $cities
     * @param string $similar
     * @param int $ndc
     * @return array
     */
    public function actionGetFreeNumbersByNdc(
        array $regions = [],
        $numberType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $mask = null,
        $offset = 0,
        $limit = FreeNumberFilter::FREE_NUMBERS_LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $similar = null,
        $ndc = null
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
            ->setSimilar($similar)
            ->setNdc($ndc)
            ->orderBy(['number' => SORT_ASC]);

        if ((int)$offset) {
            $numbers->setOffset((int)$offset);
        }

        if ((int)$numberType) {
            $numbers->setType((int)$numberType);
        }

        $response = [];

        $distinctNdcs = $numbers->getDistinctNdc();
        foreach ($distinctNdcs as $distinctNdc) {

            $numbersCloned = clone $numbers;
            $numbersCloned->setNdc($distinctNdc);

            $responseTmp = [];

            foreach ($numbersCloned->result($limit) as $freeNumberFilter) {
                $responseTmp[] = $numbersCloned->formattedNumber($freeNumberFilter, $currency);
            }

            $response[] = [
                'ndc' => (int)$distinctNdc,
                'numbers' => $responseTmp,
            ];
        }

        return $response;
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
     *
     * @SWG\Get(tags={"Список DID групп"}, path="/open/did-groups", summary="Получение DID групп", operationId="Получение DID групп",
     *   @SWG\Parameter(name="id[0]", type="integer", description="идентификатор(ы) DID групп", in="query", default="",),
     *   @SWG\Parameter(name="id[1]", type="integer", description="идентификатор(ы) DID групп", in="query", default=""),
     *   @SWG\Parameter(name="id[2]", type="integer", description="идентификатор(ы) DID групп", in="query", default=""),
     *   @SWG\Response(response=200, description="Список DID групп", @SWG\Definition(ref="#/definitions/did_group")),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     *
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