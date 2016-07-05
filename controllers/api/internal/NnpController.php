<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\traits\GetListTrait;
use app\exceptions\web\NotImplementedHttpException;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;
use Yii;

class NnpController extends ApiInternalController
{
    use IdNameRecordTrait;

    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(tags = {"Национальный номерной план"}, path = "/internal/nnp/get-operators", summary = "Операторы", operationId = "Операторы",
     *
     *   @SWG\Response(response = 200, description = "Список операторов (Билайн, Мегафон, МТС, Теле2 и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return array
     */
    public function actionGetOperators()
    {
        $query = Operator::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"Национальный номерной план"}, path = "/internal/nnp/get-regions", summary = "Регионы", operationId = "Регионы",
     *
     *   @SWG\Response(response = 200, description = "Список регионов",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return array
     */
    public function actionGetRegions()
    {
        $query = Region::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "nnpNumberRangeRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC (ABC/DEF). Код города/оператора"),
     *   @SWG\Property(property = "number_from", type = "integer", description = "Номер с"),
     *   @SWG\Property(property = "number_to", type = "integer", description = "Номер по"),
     *   @SWG\Property(property = "operator_source", type = "string", description = "Исходный оператор"),
     *   @SWG\Property(property = "operator", type = "object", description = "Оператор", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "region_source", type = "string", description = "Исходный регион"),
     *   @SWG\Property(property = "region", type = "object", description = "Регион", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "city", type = "object", description = "Город", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "is_mob", type = "integer", description = "Мобильный? 0 - стационарный ABC, 1 - мобильный DEF"),
     *   @SWG\Property(property = "is_active", type = "integer", description = "Активен? 0 - выключен, 1 - активен"),
     * ),
     *
     * @SWG\Get(tags = {"Национальный номерной план"}, path = "/internal/nnp/get-number-ranges", summary = "Диапазоны номеров", operationId = "Диапазоны номеров",
     *   @SWG\Parameter(name = "country_id", type = "integer", description = "ID страны (не префикс!)", in = "query"),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC (ABC/DEF). Код города/оператора. Обычно 3 символа", in = "query"),
     *   @SWG\Parameter(name = "number", type = "integer", description = "Номер без префиксов. Обычно 7 символов", in = "query"),
     *   @SWG\Parameter(name = "operator_id", type = "integer", description = "ID оператора. Также можно указать -1 для пустого или -2 для любого", in = "query"),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона. Также можно указать -1 для пустого или -2 для любого", in = "query"),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города. Также можно указать -1 для пустого или -2 для любого", in = "query"),
     *   @SWG\Parameter(name = "is_mob", type = "integer", description = "Мобильный? 0 - стационарный ABC, 1 - мобильный DEF", in = "query"),
     *   @SWG\Parameter(name = "is_active", type = "integer", description = "Активен? 0 - выключен, 1 - активен", in = "query"),
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
     * @return array
     */
    public function actionGetNumberRanges(
        $country_id = '',
        $ndc = '',
        $number = '',
        $operator_id = '',
        $region_id = '',
        $city_id = '',
        $is_mob = '',
        $is_active = ''
    ) {

        $query = NumberRange::find();
        $numberRangeTableName = NumberRange::tableName();

        $country_id && $query->andWhere([$numberRangeTableName . '.country_code' => $country_id]);
        $ndc && $query->andWhere([$numberRangeTableName . '.ndc' => $ndc]);

        $is_mob !== '' && $query->andWhere([$numberRangeTableName . '.is_mob' => (bool)$is_mob]);
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

        if ($number) {
            $query->andWhere(['<=', $numberRangeTableName . '.number_from', $number]);
            $query->andWhere(['>=', $numberRangeTableName . '.number_to', $number]);
        }


        $result = [];
        foreach ($query->each() as $numberRange) {
            /** @var NumberRange $numberRange */
            $result[] = $this->getNumberRangeRecord($numberRange);
        }

        return $result;
    }

    /**
     * @param NumberRange $numberRange
     * @return array
     */
    private function getNumberRangeRecord(NumberRange $numberRange)
    {
        return [
            'id' => $numberRange->id,
            'country' => $this->getIdNameRecord($numberRange->country, 'code'),
            'ndc' => $numberRange->ndc,
            'number_from' => $numberRange->number_from,
            'number_to' => $numberRange->number_to,
            'operator_source' => $numberRange->operator_source,
            'operator' => $this->getIdNameRecord($numberRange->operator),
            'region_source' => $numberRange->region_source,
            'region' => $this->getIdNameRecord($numberRange->region),
            'city' => $this->getIdNameRecord($numberRange->city),
            'is_mob' => (int)$numberRange->is_mob,
            'is_active' => (int)$numberRange->is_active,
        ];
    }
}