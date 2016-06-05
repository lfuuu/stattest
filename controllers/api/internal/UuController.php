<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffResource;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use Yii;
use yii\db\ActiveRecord;

/**
 * @SWG\Definition(definition = "idNameRecord", type = "object",
 *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор"),
 *   @SWG\Property(property = "name", type = "string", description = "Название"),
 * ),
 */
class UuController extends ApiInternalController
{

    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Definition(definition = "tariffResourceRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор"),
     *   @SWG\Property(property = "amount", type = "number", description = "Включено, ед."),
     *   @SWG\Property(property = "pricePerUnit", type = "number", description = "Цена за превышение, у.е./ед."),
     *   @SWG\Property(property = "priceMin", type = "number", description = "Мин. стоимость за месяц, у.е."),
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс (дисковое пространство, абоненты, линии и пр.)", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffPeriodRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор"),
     *   @SWG\Property(property = "priceSetup", type = "number", description = "Цена подключения, у.е."),
     *   @SWG\Property(property = "pricePerPeriod", type = "number", description = "Цена за период, у.е."),
     *   @SWG\Property(property = "priceMin", type = "number", description = "Мин. стоимость ресурсов за период, у.е."),
     *   @SWG\Property(property = "period", type = "object", description = "Период абонентки (посуточно, помесячно)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "chargePeriod", type = "object", description = "Период списания (посуточно, помесячно)", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "countOfValidityPeriod", type = "integer", description = "Кол-во периодов"),
     *   @SWG\Property(property = "isAutoprolongation", type = "integer", description = "Автопролонгация"),
     *   @SWG\Property(property = "isChargeAfterBlocking", type = "integer", description = "Списывать после блокировки"),
     *   @SWG\Property(property = "isChargeAfterPeriod", type = "integer", description = "Списывать в конце периода"),
     *   @SWG\Property(property = "isIncludeVat", type = "integer", description = "Включить НДС"),
     *   @SWG\Property(property = "isDefault", type = "integer", description = "По умолчанию"),
     *   @SWG\Property(property = "currency", type = "string", description = "Валюта"),
     *   @SWG\Property(property = "serviceType", type = "object", description = "Тип услуги (ВАТС, телефония, интернет и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariffStatus", type = "object", description = "Статус (публичный, специальный, архивный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariffPerson", type = "object", description = "Для кого действует тариф (для всех, физиков, юриков)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariffResources", type = "array", description = "Ресурсы (дисковое пространство, абоненты, линии и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffResourceRecord")),
     *   @SWG\Property(property = "tariffPeriods", type = "array", description = "Периоды (посуточно, помесячно и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "voipTarificate", type = "object", description = "Тип тарификации телефонии (посекундный, поминутный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voipGroup", type = "object", description = "Группа телефонии (местные, междугородние, международные и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voipCities", type = "array", description = "Города", @SWG\Items(ref = "#/definitions/idNameRecord")),
     * ),
     *
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariffs", summary = "Список тарифов", operationId = "Список тарифов",
     *   @SWG\Parameter(name = "id", type = "integer", description = "Идентификатор", in = "formData"),
     *   @SWG\Parameter(name = "serviceTypeId", type = "integer", description = "Идентификатор типа услуги", in = "formData"),
     *   @SWG\Parameter(name = "countryId", type = "integer", description = "Идентификатор страны", in = "formData"),
     *   @SWG\Parameter(name = "currency", type = "string", description = "Код валюты", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Список универсальных тарифов",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/tariffRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetTariffs()
    {
        $requestData = $this->requestParams;

        $tariffQuery = Tariff::find();
        isset($requestData['id']) && $tariffQuery->andWhere(['id' => (int)$requestData['id']]);
        isset($requestData['serviceTypeId']) && $tariffQuery->andWhere(['service_type_id' => (int)$requestData['serviceTypeId']]);
        isset($requestData['countryId']) && $tariffQuery->andWhere(['country_id' => (int)$requestData['countryId']]);
        isset($requestData['currency']) && $tariffQuery->andWhere(['currency_id' => $requestData['currency']]);

        $result = [];
        foreach ($tariffQuery->each() as $tariff) {
            /** @var Tariff $tariff */
            $result[] = $this->getTariffRecord($tariff);
        }

        return $result;
    }

    /**
     * @param Tariff $tariff
     * @return array
     */
    private function getTariffRecord(Tariff $tariff)
    {
        return [
            'id' => $tariff->id,
            'name' => $tariff->name,
            'countOfValidityPeriod' => $tariff->count_of_validity_period,
            'isAutoprolongation' => $tariff->is_autoprolongation,
            'isChargeAfterBlocking' => $tariff->is_charge_after_blocking,
            'isChargeAfterPeriod' => $tariff->is_charge_after_period,
            'isIncludeVat' => $tariff->is_include_vat,
            'isDefault' => $tariff->is_default,
            'currency' => $tariff->currency_id,
            'serviceType' => $this->getIdNameRecord($tariff->serviceType),
            'country' => $this->getIdNameRecord($tariff->country, 'code'),
            'tariffStatus' => $this->getIdNameRecord($tariff->status),
            'tariffPerson' => $this->getIdNameRecord($tariff->group),
            'tariffResources' => $this->getTariffResourceRecord($tariff->tariffResources),
            'tariffPeriods' => $this->getTariffPeriodRecord($tariff->tariffPeriods),
            'voipTarificate' => $this->getIdNameRecord($tariff->voipTarificate),
            'voipGroup' => $this->getIdNameRecord($tariff->voipGroup),
            'voipCities' => $this->getIdNameRecord($tariff->voipCities),
        ];
    }

    /**
     * @param ActiveRecord|ActiveRecord[] $model
     * @return array
     */
    private function getIdNameRecord($model, $idFieldName = 'id')
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getIdNameRecord($subModel, $idFieldName);
            }
            return $result;

        } elseif ($model) {

            return [
                'id' => $model->{$idFieldName},
                'name' => $model->name,
            ];

        } else {

            return [];

        }
    }

    /**
     * @param TariffResource|TariffResource[] $model
     * @return array
     */
    private function getTariffResourceRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getTariffResourceRecord($subModel);
            }
            return $result;

        } elseif ($model) {

            return [
                'id' => $model->id,
                'amount' => $model->amount,
                'pricePerUnit' => $model->price_per_unit,
                'priceMin' => $model->price_min,
                'resource' => $this->getIdNameRecord($model->resource),
            ];

        } else {

            return [];

        }
    }

    /**
     * @param TariffPeriod|TariffPeriod[] $model
     * @return array
     */
    private function getTariffPeriodRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getTariffPeriodRecord($subModel);
            }
            return $result;

        } elseif ($model) {

            return [
                'id' => $model->id,
                'priceSetup' => $model->price_setup,
                'pricePerPeriod' => $model->price_per_period,
                'priceMin' => $model->price_min,
                'period' => $this->getIdNameRecord($model->period),
                'chargePeriod' => $this->getIdNameRecord($model->chargePeriod),
            ];

        } else {

            return [];

        }
    }

}