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
     *   @SWG\Property(property = "numberFrom", type = "integer", description = "Номер с"),
     *   @SWG\Property(property = "numberTo", type = "integer", description = "Номер по"),
     *   @SWG\Property(property = "operatorSource", type = "string", description = "Исходный оператор"),
     *   @SWG\Property(property = "operator", type = "object", description = "Оператор", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "regionSource", type = "string", description = "Исходный регион"),
     *   @SWG\Property(property = "region", type = "object", description = "Регион", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "city", type = "object", description = "Город", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "isMob", type = "integer", description = "Мобильный? 0 - стационарный ABC, 1 - мобильный DEF"),
     *   @SWG\Property(property = "isActive", type = "integer", description = "Активен? 0 - выключен, 1 - активен"),
     * ),
     *
     * @SWG\Get(tags = {"Национальный номерной план"}, path = "/internal/nnp/get-number-ranges", summary = "Диапазоны номеров", operationId = "Диапазоны номеров",
     *   @SWG\Parameter(name = "countryId", type = "integer", description = "ID страны (не префикс!)", in = "query"),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC (ABC/DEF). Код города/оператора. Обычно 3 символа", in = "query"),
     *   @SWG\Parameter(name = "number", type = "integer", description = "Номер без префиксов. Обычно 7 символов", in = "query"),
     *   @SWG\Parameter(name = "operatorId", type = "integer", description = "ID оператора. Также можно указать -1 для пустого или -2 для любого", in = "query"),
     *   @SWG\Parameter(name = "regionId", type = "integer", description = "ID региона. Также можно указать -1 для пустого или -2 для любого", in = "query"),
     *   @SWG\Parameter(name = "cityId", type = "integer", description = "ID города. Также можно указать -1 для пустого или -2 для любого", in = "query"),
     *   @SWG\Parameter(name = "isMob", type = "integer", description = "Мобильный? 0 - стационарный ABC, 1 - мобильный DEF", in = "query"),
     *   @SWG\Parameter(name = "isActive", type = "integer", description = "Активен? 0 - выключен, 1 - активен", in = "query"),
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
        $countryId = '',
        $ndc = '',
        $number = '',
        $operatorId = '',
        $regionId = '',
        $cityId = '',
        $isMob = '',
        $isActive = ''
    ) {

        $query = NumberRange::find();
        $numberRangeTableName = NumberRange::tableName();

        $countryId && $query->andWhere([$numberRangeTableName . '.country_code' => $countryId]);
        $ndc && $query->andWhere([$numberRangeTableName . '.ndc' => $ndc]);

        $isMob !== '' && $query->andWhere([$numberRangeTableName . '.is_mob' => (bool)$isMob]);
        $isActive !== '' && $query->andWhere([$numberRangeTableName . '.is_active' => (bool)$isActive]);

        switch ($operatorId) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.operator_id' => $operatorId]);
                break;
        }

        switch ($regionId) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.region_id' => $regionId]);
                break;
        }

        switch ($cityId) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberRangeTableName . '.city_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberRangeTableName . '.city_id' => $cityId]);
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
            'numberFrom' => $numberRange->number_from,
            'numberTo' => $numberRange->number_to,
            'operatorSource' => $numberRange->operator_source,
            'operator' => $this->getIdNameRecord($numberRange->operator),
            'regionSource' => $numberRange->region_source,
            'region' => $this->getIdNameRecord($numberRange->region),
            'city' => $this->getIdNameRecord($numberRange->city),
            'isMob' => (int)$numberRange->is_mob,
            'isActive' => (int)$numberRange->is_active,
        ];
    }
}