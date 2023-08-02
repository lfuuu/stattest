<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\helpers\DependecyHelper;
use app\classes\traits\GetListTrait;
use app\exceptions\web\NotImplementedHttpException;
use app\models\Number;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;
use yii\base\InvalidParamException;
use yii\db\Expression;

class NnpController extends ApiInternalController
{
    use IdNameRecordTrait;

    const LIMIT = 1000;

    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Definition(definition = "nnpCountryRecord", type = "object",
     *   @SWG\Property(property = "code", type = "integer", description = "Код"),
     *   @SWG\Property(property = "name", type = "string", description = "Эндоним"),
     *   @SWG\Property(property = "name_rus", type = "string", description = "Русское название"),
     *   @SWG\Property(property = "name_eng", type = "string", description = "Английское название"),
     *   @SWG\Property(property = "prefixes", type = "string", description = "Префиксы в стиле PostgreSQL int[], то есть в фигурных скобках числа через запятую"),
     * ),
     *
     * @SWG\Get(tags = {"NationalNumberPlan"}, path = "/internal/nnp/get-countries", summary = "Страны", operationId = "Countries",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID (не префикс!)", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список стран",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/nnpCountryRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $code
     * @return array
     */
    public function actionGetCountries($code = null)
    {
        $query = Country::find();
        $code && $query->andWhere(['code' => $code]);

        $result = [];
        /** @var Country $model */
        foreach ($query->each() as $model) {
            $result[] = $this->_getNnpCountryRecord($model);
        }

        return $result;
    }

    /**
     * @param Country $country
     * @return array
     */
    private function _getNnpCountryRecord($country)
    {
        return [
            'code' => $country->code,
            'name' => $country->name,
            'name_rus' => $country->name_rus,
            'name_eng' => $country->name_eng,
            'prefixes' => $country->prefixes,
        ];
    }

    /**
     * @SWG\Definition(definition = "nnpRegionRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/nnpCountryRecord"),
     * ),
     *
     * @SWG\Get(tags = {"NationalNumberPlan"}, path = "/internal/nnp/get-regions", summary = "Регионы", operationId = "Regions",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *   @SWG\Parameter(name = "country_code", type = "integer", description = "Код страны", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список регионов",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/nnpRegionRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $id
     * @param int $country_code
     * @return array
     */
    public function actionGetRegions($id = null, $country_code = null)
    {
        $query = Region::find();
        $id && $query->andWhere(['id' => $id]);
        $country_code && $query->andWhere(['country_code' => $country_code]);

        $result = [];
        /** @var Region $model */
        foreach ($query->each() as $model) {
            $result[] = $this->_getNnpRegionRecord($model);
        }

        return $result;
    }

    /**
     * @param Region $region
     * @return array
     */
    private function _getNnpRegionRecord($region)
    {
        return [
            'id' => $region->id,
            'name' => $region->name,
            'country' => $this->_getNnpCountryRecord($region->country),
        ];
    }

    /**
     * @SWG\Definition(definition = "nnpCityRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/nnpCountryRecord"),
     *   @SWG\Property(property = "region", type = "object", description = "Регион", ref = "#/definitions/nnpRegionRecord"),
     * ),
     *
     * @SWG\Get(tags = {"NationalNumberPlan"}, path = "/internal/nnp/get-cities", summary = "Города", operationId = "Cities",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *   @SWG\Parameter(name = "country_code", type = "integer", description = "Код страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список городов",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/nnpCityRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $id
     * @param int $country_code
     * @param int $region_id
     * @return array
     */
    public function actionGetCities($id = null, $country_code = null, $region_id = null)
    {
        $query = City::find();
        $id && $query->andWhere(['id' => $id]);
        $country_code && $query->andWhere(['country_code' => $country_code]);
        $region_id && $query->andWhere(['region_id' => $region_id]);

        $result = [];
        /** @var City $model */
        foreach ($query->each() as $model) {
            $result[] = $this->_getNnpCityRecord($model);
        }

        return $result;
    }

    /**
     * @param City $country
     * @return array
     */
    private function _getNnpCityRecord($country)
    {
        return [
            'id' => $country->id,
            'name' => $country->name,
            'country' => $this->_getNnpCountryRecord($country->country),
            'region' => $this->_getNnpRegionRecord($country->region),
        ];
    }

    /**
     * @SWG\Definition(definition = "nnpOperatorRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/nnpCountryRecord"),
     * ),
     *
     * @SWG\Get(tags = {"NationalNumberPlan"}, path = "/internal/nnp/get-operators", summary = "Операторы", operationId = "Operators",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *   @SWG\Parameter(name = "country_code", type = "integer", description = "Код страны", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список операторов (Билайн, Мегафон, МТС, Теле2 и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/nnpOperatorRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $id
     * @param int $country_code
     * @return array
     */
    public function actionGetOperators($id = null, $country_code = null)
    {
        $query = Operator::find();
        $id && $query->andWhere(['id' => $id]);
        $country_code && $query->andWhere(['country_code' => $country_code]);

        $result = [];
        /** @var Operator $model */
        foreach ($query->each() as $model) {
            $result[] = $this->_getNnpOperatorRecord($model);
        }

        return $result;
    }

    /**
     * @param Operator $operator
     * @return array
     */
    private function _getNnpOperatorRecord($operator)
    {
        return [
            'id' => $operator->id,
            'name' => $operator->name,
            'country' => $this->_getNnpCountryRecord($operator->country),
        ];
    }

    /**
     * @SWG\Get(tags = {"NationalNumberPlan"}, path = "/internal/nnp/get-ndc-types", summary = "Типы NDC", operationId = "NdcTypes",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список типов NDC (сотовые, географические, короткие номера, бесплатные и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $id
     * @return array
     */
    public function actionGetNdcTypes($id = null)
    {
        $query = NdcType::find();
        $id && $query->andWhere(['id' => $id]);

        $result = [];
        /** @var NdcType $model */
        foreach ($query->each() as $model) {
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "nnpNumberRangeRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC (ABC/DEF). Код города/оператора"),
     *   @SWG\Property(property = "full_number_from", type = "integer", description = "Полный номер с"),
     *   @SWG\Property(property = "full_number_to", type = "integer", description = "Полный номер по"),
     *   @SWG\Property(property = "number_from", type = "integer", description = "Номер с"),
     *   @SWG\Property(property = "number_to", type = "integer", description = "Номер по"),
     *   @SWG\Property(property = "operator_source", type = "string", description = "Исходный оператор"),
     *   @SWG\Property(property = "operator", type = "object", description = "Оператор", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "region_source", type = "string", description = "Исходный регион"),
     *   @SWG\Property(property = "city_source", type = "string", description = "Исходный город"),
     *   @SWG\Property(property = "region", type = "object", description = "Регион", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "city", type = "object", description = "Город", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "ndc_type", type = "object", description = "Тип NDC", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "is_active", type = "integer", description = "Активен? 0 - выключен, 1 - активен"),
     * ),
     *
     * @SWG\Get(tags = {"NationalNumberPlan"}, path = "/internal/nnp/get-number-ranges", summary = "Диапазоны номеров", operationId = "NumberRange",
     *   @SWG\Parameter(name = "country_code", type = "integer", description = "Код страны (не префикс!)", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC (ABC/DEF). Код города/оператора. Обычно 3 символа", in = "query", default = ""),
     *   @SWG\Parameter(name = "number_full", type = "integer", description = "Полный номер. Например, 79991234567", in = "query", default = ""),
     *   @SWG\Parameter(name = "number", type = "integer", description = "Номер без префикса страны и без NDC. Например, 1234567", in = "query", default = ""),
     *   @SWG\Parameter(name = "operator_id", type = "integer", description = "ID оператора. Также можно указать -1 для пустого или -2 для любого", in = "query", default = ""),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона. Также можно указать -1 для пустого или -2 для любого", in = "query", default = ""),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города. Также можно указать -1 для пустого или -2 для любого", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc_type_id", type = "integer", description = "ID типа NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "is_active", type = "integer", description = "Активен? 0 - выключен, 1 - активен", in = "query", default = "1"),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Не более 1000 записей. Можно только уменьшить", in = "query", default = "1000"),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Сдвиг при пагинации. Если не указано - 0", in = "query", default = "0"),
     *
     *   @SWG\Response(response = 200, description = "Диапазоны номеров",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/nnpNumberRangeRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   ),
     * ),
     */
    /**
     * @param string $country_code
     * @param string $ndc
     * @param string $number_full
     * @param string $number
     * @param string $operator_id
     * @param string $region_id
     * @param string $city_id
     * @param bool|string $ndc_type_id
     * @param bool|string $is_active
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function actionGetNumberRanges(
        $country_code = '',
        $ndc = '',
        $number_full = '',
        $number = '',
        $operator_id = '',
        $region_id = '',
        $city_id = '',
        $ndc_type_id = '',
        $is_active = '1',
        $limit = self::LIMIT,
        $offset = 0
    )
    {

        return [];

        $query = NumberRange::find();
        $numberRangeTableName = NumberRange::tableName();

        $country_code && $query->andWhere([$numberRangeTableName . '.country_code' => $country_code]);
        $ndc && $query->andWhere([$numberRangeTableName . '.ndc' => $ndc]);

        $ndc_type_id !== '' && $query->andWhere([$numberRangeTableName . '.ndc_type_id' => $ndc_type_id]);
        $is_active !== '' && $query->andWhere([$numberRangeTableName . '.is_active' => (bool)$is_active]);

        switch ($operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.operator_id' => $operator_id]);
                break;
        }

        switch ($region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.region_id' => $region_id]);
                break;
        }

        switch ($city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.city_id' => $city_id]);
                break;
        }

        if ($number_full) {
            $query->andWhere(['<=', $numberRangeTableName . '.full_number_from', $number_full]);
            $query->andWhere(['>=', $numberRangeTableName . '.full_number_to', $number_full]);
        }

        if ($number) {
            $query->andWhere(['<=', $numberRangeTableName . '.number_from', $number]);
            $query->andWhere(['>=', $numberRangeTableName . '.number_to', $number]);
        }

        if ($number_full) {
            $numberInfo = Number::getNnpInfo($number_full);

            if ($numberInfo) {
                $cahceKey = 'nnpCountryId:' . $numberInfo['country_code'];
                if (!\Yii::$app->cache->exists($cahceKey)) {
                    $countryNumberRangeId = NumberRange::find()->select('id')->where([
                        'country_code' => $numberInfo['country_code'],
                        'is_active' => true,
                        'city_id' => null,
                    ])
                        ->andWhere(['<=', $numberRangeTableName . '.full_number_from', $number_full])
                        ->andWhere(['>=', $numberRangeTableName . '.full_number_to', $number_full])
                        ->scalar();

                    if ($countryNumberRangeId) {
                        \Yii::$app->cache->set($cahceKey, $countryNumberRangeId,DependecyHelper::TIMELIFE_DAY);
                    }
                }

                $countryNumberRangeId = \Yii::$app->cache->get($cahceKey);

                $query->andWhere(['id' => [$numberInfo['id'], $countryNumberRangeId ?? 0]]);
            }
        }

        $query->orderBy(new Expression('ndc IS NOT NULL DESC')); // чтобы большой диапазон по всей стране типа 0000-9999 был в конце

        $limit = (int)$limit;
        if ($limit <= 0 || $limit > self::LIMIT) {
            $limit = self::LIMIT;
        }

        $query->limit($limit);

        $offset = (int)$offset;
        $offset && $query->offset($offset);

        $result = [];
        foreach ($query->each(100, NumberRange::getDbSlave()) as $numberRange) {
            /** @var NumberRange $numberRange */
            $result[] = $this->_getNumberRangeRecord($numberRange);
        }

        return $result;
    }

    /**
     * @param NumberRange $numberRange
     * @return array
     */
    private function _getNumberRangeRecord(NumberRange $numberRange)
    {
        return [
            'id' => $numberRange->id,
            'country' => $this->_getIdNameRecord($numberRange->country, 'code'),
            'ndc' => $numberRange->ndc,
            'full_number_from' => $numberRange->full_number_from,
            'full_number_to' => $numberRange->full_number_to,
            'number_from' => $numberRange->number_from,
            'number_to' => $numberRange->number_to,
            'operator_source' => $numberRange->operator_source,
            'operator' => $this->_getIdNameRecord($numberRange->operator),
            'region_source' => $numberRange->region_source,
            'city_source' => $numberRange->city_source,
            'region' => $this->_getIdNameRecord($numberRange->region),
            'city' => $this->_getIdNameRecord($numberRange->city),
            'ndc_type' => $this->_getIdNameRecord($numberRange->ndcType),
            'is_active' => (int)$numberRange->is_active,
        ];
    }


    /**
     * @SWG\Get(tags = {"NationalNumberPlan"}, path = "/internal/nnp/check-number-by-filter", summary = "Проверить номер по фильтру", operationId = "NnpCheckNumberByFilter",
     *   @SWG\Parameter(name = "number", type = "integer", description = "Телефонный номер", in = "query", default = ""),
     *   @SWG\Parameter(name = "filter", type = "string", description = "фильтр в формате json", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Результат соответстивия номера и фильтру",
     *     @SWG\Schema(type = "string")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   ),
     * ),
     */
    /**
     * @param integer $number
     * @param string $filter
     * @return bool
     */
    public function actionCheckNumberByFilter($number, $filter)
    {
        /**
         * Пример:
         *
         * Номер:79648899998
         * Фильтр: {"country_code":["10002","643"],"region_id":["16748","81"],"city_id":["116886","103675"],"ndc_type_id":["2"],"is_list_black":true}
         */
        $number = intval($number);

        if (!$number || !$filter) {
            throw new InvalidParamException('Invalid Parameter');
        }

        $filter = json_decode($filter, true);

        if (!$filter || !isset($filter['is_list_black'])) {
            throw new InvalidParamException('Invalid Parameter');
        }

        $numberRangeQuery = NumberRange::find()
            ->where(['AND',
                ['<=', 'full_number_from', $number],
                ['>=', 'full_number_to', $number]
            ]);

        $isParamSet = false;
        foreach (['country_code', 'region_id', 'city_id', 'ndc_type_id', 'operator_id'] as $field) {
            if (isset($filter[$field]) && $filter[$field]) {
                $numberRangeQuery->andWhere([$field => $filter[$field]]);
                $isParamSet = true;
            }
        }

        if (!$isParamSet) { // без параметров
            return !$filter['is_list_black'];
        }

        $result = NumberRange::getDbSlave()->createCommand(
            NumberRange::getDbSlave()
                ->getQueryBuilder()
                ->selectExists(
                    $numberRangeQuery
                        ->createCommand()
                        ->rawSql
                )
        )
            ->cache(86400)
            ->queryScalar();

        if ($filter['is_list_black']) {
            return !$result;
        }

        return $result;
    }

}