<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\uu\model\Period;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffStatus;
use app\classes\uu\model\TariffVoipCity;
use app\classes\uu\model\TariffVoipGroup;
use app\classes\uu\model\TariffVoipTarificate;
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
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-service-types", summary = "Список типов услуг", operationId = "Список типов услуг",
     *
     *   @SWG\Response(response = 200, description = "Список типов услуг (ВАТС, телефония, интернет и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
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
    public function actionGetServiceTypes()
    {
        $query = ServiceType::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-resources", summary = "Список ресурсов", operationId = "Список ресурсов",
     *
     *   @SWG\Response(response = 200, description = "Список ресурсов (дисковое пространство, абоненты, линии и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
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
    public function actionGetResources()
    {
        $query = Resource::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-periods", summary = "Список периодов", operationId = "Список периодов",
     *
     *   @SWG\Response(response = 200, description = "Список периодов (день, месяц, год и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
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
    public function actionGetPeriods()
    {
        $query = Period::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-statuses", summary = "Список статусов тарифа", operationId = "Список статусов тарифа",
     *
     *   @SWG\Response(response = 200, description = "Список статусов тарифа (публичный, специальный, архивный и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
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
    public function actionGetTariffStatuses()
    {
        $query = TariffStatus::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-persons", summary = "Список для кого действует тариф", operationId = "Список для кого действует тариф",
     *
     *   @SWG\Response(response = 200, description = "Список для кого действует тариф (для всех, физиков, юриков)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
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
    public function actionGetTariffPersons()
    {
        $query = TariffPerson::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-voip-tarificates", summary = "Список типов тарификации телефонии", operationId = "Список типов тарификации телефонии",
     *
     *   @SWG\Response(response = 200, description = "Список типов тарификации телефонии (посекундный, поминутный и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
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
    public function actionGetTariffVoipTarificates()
    {
        $query = TariffVoipTarificate::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-voip-groups", summary = "Список групп телефонии", operationId = "Список групп телефонии",
     *
     *   @SWG\Response(response = 200, description = "Список групп телефонии (местные, междугородние, международные и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
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
    public function actionGetTariffVoipGroups()
    {
        $query = TariffVoipGroup::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->getIdNameRecord($model);
        }

        return $result;
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
     *   @SWG\Property(property = "currencyId", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)"),
     *   @SWG\Property(property = "serviceType", type = "object", description = "Тип услуги (ВАТС, телефония, интернет и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariffStatus", type = "object", description = "Статус (публичный, специальный, архивный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariffPerson", type = "object", description = "Для кого действует тариф (для всех, физиков, юриков)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariffResources", type = "array", description = "Ресурсы (дисковое пространство, абоненты, линии и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffResourceRecord")),
     *   @SWG\Property(property = "tariffPeriods", type = "array", description = "Периоды (посуточно, помесячно и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "voipTarificate", type = "object", description = "Телефония. Тип тарификации (посекундный, поминутный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voipGroup", type = "object", description = "Телефония. Группа (местные, междугородние, международные и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voipCities", type = "array", description = "Телефония. Города", @SWG\Items(ref = "#/definitions/idNameRecord")),
     * ),
     *
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariffs", summary = "Список тарифов", operationId = "Список тарифов",
     *   @SWG\Parameter(name = "id", type = "integer", description = "Идентификатор", in = "formData"),
     *   @SWG\Parameter(name = "parentId", type = "integer", description = "Идентификатор родителя. Нужен для поиска совместимых пакетов", in = "formData"),
     *   @SWG\Parameter(name = "serviceTypeId", type = "integer", description = "Идентификатор типа услуги (ВАТС, телефония, интернет и пр.)", in = "formData"),
     *   @SWG\Parameter(name = "countryId", type = "integer", description = "Идентификатор страны", in = "formData"),
     *   @SWG\Parameter(name = "currencyId", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)", in = "formData"),
     *   @SWG\Parameter(name = "voipCityId", type = "integer", description = "Идентификатор города телефонии", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Список тарифов",
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

        if (isset($requestData['parentId'])) {
            // передан родительский тариф (предполагается, что телефонии), надо найти пакеты
            /** @var Tariff $tariff */
            $tariff = Tariff::find()->where(['id' => (int)$requestData['parentId']])->one();
            if (!$tariff) {
                return [];
            }
            $requestData['serviceTypeId'] = ServiceType::ID_VOIP_PACKAGE; // других пакетов пока все равно нет
            !isset($requestData['countryId']) && $requestData['countryId'] = $tariff->country_id;
            !isset($requestData['currencyId']) && $requestData['currencyId'] = $tariff->currency_id;
            !isset($requestData['voipCityId']) && $requestData['voipCityId'] = array_keys($tariff->voipCities);
            unset($tariff);
        }

        $tariffQuery = Tariff::find();
        $tariffTableName = Tariff::tableName();
        isset($requestData['id']) && $tariffQuery->andWhere([$tariffTableName . '.id' => (int)$requestData['id']]);
        isset($requestData['serviceTypeId']) && $tariffQuery->andWhere([$tariffTableName . '.service_type_id' => (int)$requestData['serviceTypeId']]);
        isset($requestData['countryId']) && $tariffQuery->andWhere([$tariffTableName . '.country_id' => (int)$requestData['countryId']]);
        isset($requestData['currencyId']) && $tariffQuery->andWhere([$tariffTableName . '.currency_id' => $requestData['currencyId']]);

        if (isset($requestData['voipCityId'])) {
            $tariffQuery->joinWith('voipCities');
            $tariffVoipCityTableName = TariffVoipCity::tableName();
            $tariffQuery->andWhere([$tariffVoipCityTableName . '.city_id' => $requestData['voipCityId']]);
        }

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
            'voipCities' => $this->getIdNameRecord($tariff->voipCities, 'city_id'),
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
                'name' => (string)$model,
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