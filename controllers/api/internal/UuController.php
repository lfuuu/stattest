<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
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
use app\exceptions\api\internal\ExceptionValidationForm;
use app\exceptions\web\NotImplementedHttpException;
use InvalidArgumentException;
use LogicException;
use Yii;

class UuController extends ApiInternalController
{
    use IdNameRecordTrait;

    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-service-types", summary = "Список типов услуг", operationId = "Список типов услуг",
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
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-resources", summary = "Список ресурсов", operationId = "Список ресурсов",
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
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-periods", summary = "Список периодов", operationId = "Список периодов",
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
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-statuses", summary = "Список статусов тарифа", operationId = "Список статусов тарифа",
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
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-persons", summary = "Список для кого действует тариф", operationId = "Список для кого действует тариф",
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
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-voip-tarificates", summary = "Список типов тарификации телефонии", operationId = "Список типов тарификации телефонии",
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
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariff-voip-groups", summary = "Список групп телефонии", operationId = "Список групп телефонии",
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
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "amount", type = "number", description = "Включено, ед."),
     *   @SWG\Property(property = "price_per_unit", type = "number", description = "Цена за превышение, у.е./ед."),
     *   @SWG\Property(property = "price_min", type = "number", description = "Мин. стоимость за месяц, у.е."),
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс (дисковое пространство, абоненты, линии и пр.)", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffPeriodRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID. Именно его надо указывать при создании услуги"),
     *   @SWG\Property(property = "price_setup", type = "number", description = "Цена подключения, у.е."),
     *   @SWG\Property(property = "price_per_period", type = "number", description = "Цена за период, у.е."),
     *   @SWG\Property(property = "price_min", type = "number", description = "Мин. стоимость ресурсов за период, у.е."),
     *   @SWG\Property(property = "period", type = "object", description = "Период абонентки (посуточно, помесячно)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "charge_period", type = "object", description = "Период списания (посуточно, помесячно)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff", type = "object", description = "Тариф", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "count_of_validity_period", type = "integer", description = "Кол-во периодов"),
     *   @SWG\Property(property = "is_autoprolongation", type = "integer", description = "Автопролонгация"),
     *   @SWG\Property(property = "is_charge_after_blocking", type = "integer", description = "Списывать после блокировки"),
     *   @SWG\Property(property = "is_charge_after_period", type = "integer", description = "Списывать в конце периода"),
     *   @SWG\Property(property = "is_include_vat", type = "integer", description = "Включить НДС"),
     *   @SWG\Property(property = "is_default", type = "integer", description = "По умолчанию"),
     *   @SWG\Property(property = "currency_id", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)"),
     *   @SWG\Property(property = "serviceType", type = "object", description = "Тип услуги (ВАТС, телефония, интернет и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_status", type = "object", description = "Статус (публичный, специальный, архивный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_person", type = "object", description = "Для кого действует тариф (для всех, физиков, юриков)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_resources", type = "array", description = "Ресурсы (дисковое пространство, абоненты, линии и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffResourceRecord")),
     *   @SWG\Property(property = "tariff_periods", type = "array", description = "Периоды (посуточно, помесячно и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "voip_tarificate", type = "object", description = "Телефония. Тип тарификации (посекундный, поминутный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_group", type = "object", description = "Телефония. Группа (местные, междугородние, международные и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_cities", type = "array", description = "Телефония. Города", @SWG\Items(ref = "#/definitions/idNameRecord")),
     * ),
     *
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariffs", summary = "Список тарифов", operationId = "Список тарифов",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query"),
     *   @SWG\Parameter(name = "parent_id", type = "integer", description = "ID родителя. Нужен для поиска совместимых пакетов", in = "query"),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "query", required = true),
     *   @SWG\Parameter(name = "is_default", type = "integer", description = "По умолчанию (0 / 1)", in = "query"),
     *   @SWG\Parameter(name = "currency_id", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)", in = "query"),
     *   @SWG\Parameter(name = "country_id", type = "integer", description = "ID страны", in = "query"),
     *   @SWG\Parameter(name = "tariff_status_id", type = "integer", description = "ID статуса (публичный, специальный, архивный и пр.)", in = "query"),
     *   @SWG\Parameter(name = "tariff_person_id", type = "integer", description = "ID для кого действует тариф (для всех, физиков, юриков)", in = "query"),
     *   @SWG\Parameter(name = "voip_tarificate_id", type = "integer", description = "ID типа тарификации телефонии (посекундный, поминутный и пр.)", in = "query"),
     *   @SWG\Parameter(name = "voip_group_id", type = "integer", description = "ID группы телефонии (местные, междугородние, международные и пр.)", in = "query"),
     *   @SWG\Parameter(name = "voip_city_id", type = "integer", description = "ID города телефонии", in = "query"),
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
     */
    public function actionGetTariffs(
        $id = null,
        $parent_id = null,
        $service_type_id = null,
        $country_id = null,
        $currency_id = null,
        $is_default = null,
        $tariff_status_id = null,
        $tariff_person_id = null,
        $voip_tarificate_id = null,
        $voip_group_id = null,
        $voip_city_id = null
    ) {
        if ($parent_id) {
            // передан родительский тариф (предполагается, что телефонии), надо найти пакеты
            /** @var Tariff $tariff */
            $tariff = Tariff::find()->where(['id' => (int)$parent_id])->one();
            if (!$tariff) {
                return [];
            }
            $service_type_id = ServiceType::ID_VOIP_PACKAGE; // других пакетов пока все равно нет
            !$country_id && $country_id = $tariff->country_id;
            !$currency_id && $currency_id = $tariff->currency_id;
            !$voip_city_id && $voip_city_id = array_keys($tariff->voipCities);
            unset($tariff);
        }

        $tariffQuery = Tariff::find();
        $tariffTableName = Tariff::tableName();
        $id && $tariffQuery->andWhere([$tariffTableName . '.id' => (int)$id]);
        $service_type_id && $tariffQuery->andWhere([$tariffTableName . '.service_type_id' => (int)$service_type_id]);
        $country_id && $tariffQuery->andWhere([$tariffTableName . '.country_id' => (int)$country_id]);
        $currency_id && $tariffQuery->andWhere([$tariffTableName . '.currency_id' => $currency_id]);
        $is_default && $tariffQuery->andWhere([$tariffTableName . '.is_default' => (int)$is_default]);
        $tariff_status_id && $tariffQuery->andWhere([$tariffTableName . '.tariff_status_id' => (int)$tariff_status_id]);
        $tariff_person_id && $tariffQuery->andWhere([$tariffTableName . '.tariff_person_id' => (int)$tariff_person_id]);
        $voip_tarificate_id && $tariffQuery->andWhere([$tariffTableName . '.voip_tarificate_id' => (int)$voip_tarificate_id]);
        $voip_group_id && $tariffQuery->andWhere([$tariffTableName . '.voip_group_id' => (int)$voip_group_id]);

        if ($voip_city_id) {
            $tariffQuery->joinWith('voipCities');
            $tariffVoipCityTableName = TariffVoipCity::tableName();
            $tariffQuery->andWhere([$tariffVoipCityTableName . '.city_id' => $voip_city_id]);
        }

        $result = [];
        foreach ($tariffQuery->each() as $tariff) {
            /** @var Tariff $tariff */
            $result[] = $this->getTariffRecord($tariff);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "accountTariffLogRecord", type = "object",
     *   @SWG\Property(property = "tariff_period", type = "object", description = "Период тарифа. Если закрыто, то null", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "actual_from", type = "string", description = "Дата, с которой этот тариф действует. ГГГГ-ММ-ДД"),
     * ),
     *
     * @SWG\Definition(definition = "accountLogSetupRecord", type = "object",
     *   @SWG\Property(property = "date", type = "string", description = "Дата списания. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "price", type = "number", description = "Стоимость"),
     *   @SWG\Property(property = "tariff_id", type = "integer", description = "ID тарифа"),
     *   @SWG\Property(property = "tariff_period_id", type = "integer", description = "ID периода тарифа"),
     * ),
     *
     * @SWG\Definition(definition = "accountLogPeriodRecord", type = "object",
     *   @SWG\Property(property = "date_from", type = "string", description = "Дата начала диапазона списания. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "date_to", type = "string", description = "Дата окончания диапазона списания. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "price_per_period", type = "number", description = "Цена за полный период"),
     *   @SWG\Property(property = "coefficient", type = "number", description = "Коэффициент неполного периода"),
     *   @SWG\Property(property = "price", type = "number", description = "Стоимость"),
     *   @SWG\Property(property = "tariff_id", type = "integer", description = "ID тарифа"),
     *   @SWG\Property(property = "tariff_period_id", type = "integer", description = "ID периода тарифа"),
     * ),
     *
     * @SWG\Definition(definition = "accountLogResourcesRecord", type = "object",
     *   @SWG\Property(property = "date", type = "string", description = "Дата списания. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "amount_use", type = "number", description = "Потрачено ресурса"),
     *   @SWG\Property(property = "amount_free", type = "number", description = "Доступно ресурса бесплатно"),
     *   @SWG\Property(property = "amount_overhead", type = "number", description = "Платное превышение ресурса"),
     *   @SWG\Property(property = "price_per_unit", type = "number", description = "Цена единицы ресурса"),
     *   @SWG\Property(property = "price", type = "number", description = "Стоимость"),
     *   @SWG\Property(property = "tariff_id", type = "integer", description = "ID тарифа"),
     *   @SWG\Property(property = "tariff_period_id", type = "integer", description = "ID периода тарифа"),
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс (дисковое пространство, абоненты, линии и пр.)", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "accountTariffRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "client_account_id", type = "integer", description = "ID аккаунта клиента"),
     *   @SWG\Property(property = "service_type", type = "object", description = "Тип услуги (ВАТС, телефония, интернет и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "region", type = "object", description = "Регион (кроме телефонии)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "city", type = "object", description = "Город (только для телефонии)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "prev_account_tariff_id", type = "integer", description = "ID основной услуги телефонии (если это пакет телефонии)"),
     *   @SWG\Property(property = "next_account_tariffs", type = "array", description = "Услуги пакета телефонии (если это телефония)", @SWG\Items(ref = "#/definitions/accountTariffRecord")),
     *   @SWG\Property(property = "comment", type = "string", description = "Комментарий"),
     *   @SWG\Property(property = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона"),
     *   @SWG\Property(property = "account_tariff_logs", type = "array", description = "Лог тарифов", @SWG\Items(ref = "#/definitions/accountTariffLogRecord")),
     *   @SWG\Property(property = "account_log_setups", type = "array", description = "Транзакции за подключение", @SWG\Items(ref = "#/definitions/accountLogSetupRecord")),
     *   @SWG\Property(property = "account_log_periods", type = "array", description = "Транзакции за абонентскую плату", @SWG\Items(ref = "#/definitions/accountLogPeriodRecord")),
     *   @SWG\Property(property = "account_log_resources", type = "array", description = "Транзакции за ресурсы", @SWG\Items(ref = "#/definitions/accountLogResourcesRecord")),
     * ),
     *
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-account-tariffs", summary = "Список услуг у клиента", operationId = "Список услуг у клиента",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query"),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID аккаунта клиента", in = "query"),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "query"),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона (кроме телефонии)", in = "query"),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города (только для телефонии)", in = "query"),
     *   @SWG\Parameter(name = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "query"),
     *   @SWG\Parameter(name = "prev_account_tariff_id", type = "integer", description = "ID основной услуги клиента. Если список услуг пакета телефонии, то можно здесь указать ID услуги телефонии", in = "query"),
     *
     *   @SWG\Response(response = 200, description = "Список услуг у клиента",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/accountTariffRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function actionGetAccountTariffs(
        $id = null,
        $service_type_id = null,
        $client_account_id = null,
        $region_id = null,
        $city_id = null,
        $voip_number = null,
        $prev_account_tariff_id = null
    ) {
        $accountTariffQuery = AccountTariff::find();
        $accountTariffTableName = AccountTariff::tableName();
        $id && $accountTariffQuery->andWhere([$accountTariffTableName . '.id' => (int)$id]);
        $service_type_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.service_type_id' => (int)$service_type_id]);
        $client_account_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.client_account_id' => (int)$client_account_id]);
        $region_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.region_id' => (int)$region_id]);
        $city_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.city_id' => (int)$city_id]);
        $voip_number && $accountTariffQuery->andWhere([$accountTariffTableName . '.voip_number' => $voip_number]);
        $prev_account_tariff_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.prev_account_tariff_id' => $prev_account_tariff_id]);

        if (!$id && !$service_type_id && !$client_account_id) {
            throw new InvalidArgumentException('Необходимо указать фильтр id, service_type_id или client_account_id');
        }

        $result = [];
        foreach ($accountTariffQuery->each() as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            $result[] = $this->getAccountTariffRecord($accountTariff);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "accountTariffLogLightRecord", type = "object",
     *   @SWG\Property(property = "tariff_period", type = "object", description = "Период тарифа", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "activate_past_date", type = "string", description = "Дата, с которой этот тариф был включен и сейчас действует. Всегда в прошлом. Если null - еще не включен (тогда см. activate_future_date) или уже выключен (deactivate_past_date). ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "activate_future_date", type = "string", description = "Дата, с которой этот тариф будет включен, и его можно отменить. Всегда в будущем. Если null - в будущем изменений не будет. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "deactivate_past_date", type = "string", description = "Дата, с которой этот тариф был выключен, и сейчас не действует. Всегда в прошлом. Если null - не был выключен. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "deactivate_future_date", type = "string", description = "Дата, с которой этот тариф будет выключен, и его можно отменить. Всегда в будущем. Если null - в будущем изменений не будет. ГГГГ-ММ-ДД"),
     * ),
     *
     * @SWG\Definition(definition = "grouppedAccountTariffRecord", type = "object",
     *   @SWG\Property(property = "voip_city", type = "object", description = "Город", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_numbers", type = "array", description = "Номера. Если 4-5 символов - номер линии, если больше - номер телефона", @SWG\Items(type = "integer")),
     *   @SWG\Property(property = "is_cancelable", type = "boolean", description = "Можно ли отменить смену тарифа?"),
     *   @SWG\Property(property = "is_editable", type = "boolean", description = "Можно ли сменить тариф или отключить услугу?"),
     *   @SWG\Property(property = "account_tariff_logs_light", type = "array", description = "Сокращенный лог тарифов (только текущий и будущий). По убыванию даты", @SWG\Items(ref = "#/definitions/accountTariffLogLightRecord")),
     *   @SWG\Property(property = "account_tariff_logs", type = "array", description = "Лог тарифов. По убыванию даты", @SWG\Items(ref = "#/definitions/accountTariffLogRecord")),
     *   @SWG\Property(property = "next_account_tariffs", type = "array", description = "Услуги пакета телефонии (если это телефония)", @SWG\Items(ref = "#/definitions/accountTariffRecord")),
     * ),
     *
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-account-tariffs-voip", summary = "Сгруппированный список услуг телефонии у клиента", operationId = "Сгруппированный список услуг телефонии у клиента",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID аккаунта клиента", in = "query"),
     *
     *   @SWG\Response(response = 200, description = "Сгруппированный список услуг телефонии у клиента",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/grouppedAccountTariffRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return array
     * @throws \InvalidArgumentException
     *
     * адаптированный вариант views/uu/account-tariff/_indexVoip.php
     */
    public function actionGetAccountTariffsVoip(
        $client_account_id = null
    ) {
        $service_type_id = ServiceType::ID_VOIP;

        $accountTariffQuery = AccountTariff::find();
        $accountTariffTableName = AccountTariff::tableName();
        $service_type_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.service_type_id' => (int)$service_type_id]);
        $client_account_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.client_account_id' => (int)$client_account_id]);

        if (!$client_account_id) {
            throw new InvalidArgumentException('Необходимо указать фильтр client_account_id');
        }

        // сгруппировать одинаковые город-тариф-пакеты по строчкам
        $grouppedAccountTariffs = AccountTariff::getGroupedObjects($accountTariffQuery);

        $result = [];
        foreach ($grouppedAccountTariffs as $grouppedAccountTariff) {
            $result[] = $this->getGrouppedAccountTariffVoipRecord($grouppedAccountTariff);
        }

        return $result;
    }

    /**
     * @SWG\Put(tags = {"Универсальные тарифы"}, path = "/internal/uu/add-account-tariff", summary = "Добавить услугу клиенту", operationId = "Добавить услугу клиенту",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID аккаунта клиента", in = "formData", required = true),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "formData", required = true),
     *   @SWG\Parameter(name = "tariff_period_id", type = "integer", description = "ID периода тарифа (например, 100 руб/мес, 1000 руб/год)", in = "formData", required = true),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой этот тариф действует. ГГГГ-ММ-ДД. Если не указан, то с сегодня. Если с сегодня, то отменить нельзя - можно только закрыть с завтра", in = "formData"),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона (кроме телефонии)", in = "formData"),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города (только для телефонии)", in = "formData"),
     *   @SWG\Parameter(name = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "formData"),
     *   @SWG\Parameter(name = "comment", type = "string", description = "Комментарий", in = "formData"),
     *   @SWG\Parameter(name = "prev_account_tariff_id", type = "integer", description = "ID основной услуги клиента. Если добавляется услуга пакета телефонии, то необходимо здесь указать ID услуги телефонии", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Услуга клиенту добавлена",
     *     @SWG\Schema(type = "integer", description = "ID")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return int
     * @throws \app\exceptions\api\internal\ExceptionValidationForm
     */
    public function actionAddAccountTariff()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            $accountTariff = new AccountTariff();
            $accountTariff->setAttributes($post);
            if (!$accountTariff->validate() || !$accountTariff->save()) {
                throw new ExceptionValidationForm($accountTariff);
            }

            // записать в лог тарифа
            $accountTariffLog = new AccountTariffLog;
            $accountTariffLog->account_tariff_id = $accountTariff->id;
            $accountTariffLog->setAttributes($post);
            if (!$accountTariffLog->validate() || !$accountTariffLog->save()) {
                throw new ExceptionValidationForm($accountTariffLog);
            }

            $transaction->commit();
            return $accountTariff->id;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/edit-account-tariff", summary = "Сменить тариф услуге клиента", operationId = "Сменить тариф услуге клиента",
     *   @SWG\Parameter(name = "account_tariff_id", type = "integer", description = "ID услуги", in = "query", required = true),
     *   @SWG\Parameter(name = "tariff_period_id", type = "integer", description = "ID периода тарифа (например, 100 руб/мес, 1000 руб/год)", in = "formData", required = true),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой этот тариф действует. ГГГГ-ММ-ДД. Если не указано - с завтра", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Тариф изменен",
     *     @SWG\Schema(type = "integer", description = "ID записи в логе тарифов")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return int
     * @throws ExceptionValidationForm
     */
    public function actionEditAccountTariff($account_tariff_id = null)
    {
        $postData = Yii::$app->request->post();
        return $this->editAccountTariff(
            $account_tariff_id,
            $postData['tariff_period_id'],
            isset($postData['actual_from']) ? $postData['actual_from'] : null
        );
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/close-account-tariff", summary = "Закрыть услугу клиента", operationId = "Закрыть услугу клиента",
     *   @SWG\Parameter(name = "account_tariff_id", type = "integer", description = "ID услуги", in = "query", required = true),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой услуга закрывается. ГГГГ-ММ-ДД. Если не указано - с завтра", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Услуга закрыта",
     *     @SWG\Schema(type = "integer", description = "ID записи в логе тарифов")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return int
     * @throws ExceptionValidationForm
     */
    public function actionCloseAccountTariff($account_tariff_id = null)
    {
        $postData = Yii::$app->request->post();
        return $this->editAccountTariff(
            $account_tariff_id,
            null,
            isset($postData['actual_from']) ? $postData['actual_from'] : null
        );
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/cancel-edit-account-tariff", summary = "Отменить последнюю смену тарифа (или закрытие) услуги клиента", operationId = "Отменить последнюю смену тарифа (или закрытие) услуги клиента",
     *   @SWG\Parameter(name = "account_tariff_id", type = "integer", description = "ID услуги", in = "query", required = true),
     *
     *   @SWG\Response(response = 200, description = "Последняя смена тарифа (в т.ч. закрытие) услуги отменена",
     *     @SWG\Schema(type = "integer", description = "Новый последний tariffPeriodId (идентификатор периода). Если 0 - услуга удалена, ибо больше в логе тарифов ничего нет")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return int
     * @throws \InvalidArgumentException
     * @throws ExceptionValidationForm
     */
    public function actionCancelEditAccountTariff($account_tariff_id = null)
    {
        if (!$account_tariff_id) {
            throw new InvalidArgumentException('Не указан обязательный параметр');
        }
        $accountTariff = AccountTariff::findOne(['id' => (int)$account_tariff_id]);
        if (!$accountTariff) {
            throw new InvalidArgumentException('Услуга с таким идентификатором не найдена');
        }
        if (!$accountTariff->isCancelable()) {
            throw new LogicException('Нельзя отменить уже примененный тариф');
        }

        // лог тарифов
        $accountTariffLogs = $accountTariff->accountTariffLogs;

        // отменяемый тариф
        /** @var AccountTariffLog $accountTariffLogCancelled */
        $accountTariffLogCancelled = array_shift($accountTariffLogs);
        if (strtotime($accountTariffLogCancelled->actual_from) < time()) {
            throw new LogicException('Нельзя отменить уже примененный тариф');
        }

        if (!count($accountTariffLogs)) {

            // услуга еще даже не начинала действовать, текущего тарифа нет - удалить услугу полностью. Лог тарифов должен удалиться каскадно
            if (!$accountTariff->delete()) {
                throw new ExceptionValidationForm($accountTariff);
            }

            return 0;

        } else {

            // отменить (удалить) последний тариф
            if (!$accountTariffLogCancelled->delete()) {
                throw new ExceptionValidationForm($accountTariffLogCancelled);
            }

            // предпоследний тариф становится текущим
            /** @var AccountTariffLog $accountTariffLogActual */
            $accountTariffLogActual = array_shift($accountTariffLogs);

            // у услуги сменить кэш тарифа
            $accountTariff->tariff_period_id = $accountTariffLogActual->tariff_period_id;
            if (!$accountTariff->save()) {
                throw new ExceptionValidationForm($accountTariff);
            }

            return $accountTariff->tariff_period_id;
        }
    }

    /**
     * @param $account_tariff_id
     * @param $tariff_period_id
     * @param $actual_from
     * @return int
     * @throws ExceptionValidationForm
     */
    public function editAccountTariff($account_tariff_id, $tariff_period_id, $actual_from)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {

            $accountTariff = AccountTariff::findOne(['id' => (int)$account_tariff_id]);
            if (!$accountTariff) {
                throw new InvalidArgumentException('Услуга с таким идентификатором не найдена ' . $account_tariff_id);
            }

            // у услуги сменить кэш тарифа
            $accountTariff->tariff_period_id = $tariff_period_id;
            if (!$accountTariff->save()) {
                throw new ExceptionValidationForm($accountTariff);
            }

            // записать в лог тарифа
            $accountTariffLog = new AccountTariffLog;
            $accountTariffLog->account_tariff_id = $accountTariff->id;
            $accountTariffLog->tariff_period_id = $tariff_period_id;
            if (!$accountTariffLog->save()) {
                throw new ExceptionValidationForm($accountTariffLog);
            }

            $transaction->commit();
            return $accountTariffLog->id;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
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
            'count_of_validity_period' => $tariff->count_of_validity_period,
            'is_autoprolongation' => $tariff->is_autoprolongation,
            'is_charge_after_blocking' => $tariff->is_charge_after_blocking,
            'is_charge_after_period' => $tariff->is_charge_after_period,
            'is_include_vat' => $tariff->is_include_vat,
            'is_default' => $tariff->is_default,
            'currency' => $tariff->currency_id,
            'service_type' => $this->getIdNameRecord($tariff->serviceType),
            'country' => $this->getIdNameRecord($tariff->country, 'code'),
            'tariff_status' => $this->getIdNameRecord($tariff->status),
            'tariff_person' => $this->getIdNameRecord($tariff->group),
            'tariff_resources' => $this->getTariffResourceRecord($tariff->tariffResources),
            'tariff_periods' => $this->getTariffPeriodRecord($tariff->tariffPeriods),
            'voip_tarificate' => $this->getIdNameRecord($tariff->voipTarificate),
            'voip_group' => $this->getIdNameRecord($tariff->voipGroup),
            'voip_cities' => $this->getIdNameRecord($tariff->voipCities, 'city_id'),
        ];
    }

    /**
     * @param AccountTariff|AccountTariff[] $accountTariff
     * @return array
     */
    private function getAccountTariffRecord($accountTariff)
    {
        if (is_array($accountTariff)) {

            $result = [];
            foreach ($accountTariff as $subAccountTariff) {
                $result[] = $this->getAccountTariffRecord($subAccountTariff);
            }
            return $result;

        }

        return [
            'id' => $accountTariff->id,
            'client_account_id' => $accountTariff->client_account_id,
            'service_type' => $this->getIdNameRecord($accountTariff->serviceType),
            'region' => $this->getIdNameRecord($accountTariff->region),
            'city' => $this->getIdNameRecord($accountTariff->city),
            'prev_account_tariff_id' => $accountTariff->prev_account_tariff_id,
            'next_account_tariffs' => $this->getAccountTariffRecord($accountTariff->nextAccountTariffs),
            'comment' => $accountTariff->comment,
            'voip_number' => $accountTariff->voip_number,
            'account_tariff_logs' => $this->getAccountTariffLogRecord($accountTariff->accountTariffLogs),
            'account_log_setups' => $this->getAccountLogSetupRecord($accountTariff->accountLogSetups),
            'account_log_periods' => $this->getAccountLogPeriodRecord($accountTariff->accountLogPeriods),
            'account_log_resources' => $this->getAccountLogResourceRecord($accountTariff->accountLogResources),
        ];
    }

    /**
     * Сгруппированные услуги. Отличаются только номером
     *
     * @param AccountTariff[] $accountTariffs
     * @return array
     */
    private function getGrouppedAccountTariffVoipRecord($accountTariffs)
    {
        /** @var AccountTariff $accountTariffFirst */
        $accountTariffFirst = reset($accountTariffs);

        $numbers = [];
        foreach ($accountTariffs as $accountTariff) {
            $numbers[] = $accountTariff->voip_number;
        }

        return [
            'voip_city' => $this->getIdNameRecord($accountTariffFirst->city),
            'voip_numbers' => $numbers,
            'is_cancelable' => $accountTariffFirst->isCancelable(), // Можно ли отменить смену тарифа?
            'is_editable' => (bool)$accountTariffFirst->tariff_period_id, // Можно ли сменить тариф или отключить услугу?
            'account_tariff_logs_light' => $this->getAccountTariffLogLightRecord($accountTariffFirst->accountTariffLogs),
            'account_tariff_logs' => $this->getAccountTariffLogRecord($accountTariffFirst->accountTariffLogs),
            'next_account_tariffs' => $this->getAccountTariffRecord($accountTariffFirst->nextAccountTariffs),
        ];
    }

    /**
     * @param TariffResource|TariffResource[] $model
     * @return array|null
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
                'price_per_unit' => $model->price_per_unit,
                'price_min' => $model->price_min,
                'resource' => $this->getIdNameRecord($model->resource),
            ];

        } else {

            return null;

        }
    }

    /**
     * @param TariffPeriod|TariffPeriod[] $model
     * @return array|null
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
                'price_setup' => $model->price_setup,
                'price_per_period' => $model->price_per_period,
                'price_min' => $model->price_min,
                'period' => $this->getIdNameRecord($model->period),
                'charge_period' => $this->getIdNameRecord($model->chargePeriod),
                'tariff' => $this->getIdNameRecord($model->tariff),
            ];

        } else {

            return null;

        }
    }

    /**
     * @param AccountTariffLog|AccountTariffLog[] $model
     * @return array|null
     */
    private function getAccountTariffLogRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getAccountTariffLogRecord($subModel);
            }
            return $result;

        } elseif ($model) {

            return [
                'tariff_period' => $this->getTariffPeriodRecord($model->tariffPeriod),
                'actual_from' => $model->actual_from,
            ];

        } else {

            return null;

        }
    }

    /**
     * @param AccountTariffLog[] $model
     * @return array
     */
    private function getAccountTariffLogLightRecord($models)
    {
        $result = [];

        /** @var AccountTariffLog $model */
        $model = array_shift($models);
        if (!$model) {
            return $result;
        }
        $isCancelable = $model->actual_from > date('Y-m-d');
        $result[] = [
            'tariff_period' => $this->getTariffPeriodRecord($model->tariffPeriod),
            'activate_past_date' => ($model->tariff_period_id && !$isCancelable) ? $model->actual_from : null, // смена тарифа в прошлом,
            'activate_future_date' => ($model->tariff_period_id && $isCancelable) ? $model->actual_from : null, // смена тарифа в будущем
            'deactivate_past_date' => (!$model->tariff_period_id && !$isCancelable) ? $model->actual_from : null, // закрытие тарифа в прошлом,
            'deactivate_future_date' => (!$model->tariff_period_id && $isCancelable) ? $model->actual_from : null, // закрытие тарифа в будущем
        ];

        if (!($model->tariff_period_id && $isCancelable)) {
            // только для "смена тарифа в будущем" выведем предыдущий тариф. А для всего остального больше ничего не надо
            return $result;
        }

        /** @var AccountTariffLog $model */
        $model = array_shift($models);
        if (!$model) {
            return $result;
        }
        $result[] = [
            'tariff_period' => $this->getTariffPeriodRecord($model->tariffPeriod),
            'activate_past_date' => $model->actual_from, // обычная смена тарифа в прошлом,
            'activate_future_date' => null,
            'deactivate_past_date' => null,
            'deactivate_future_date' => null,
        ];
        return $result;
    }

    /**
     * @param AccountLogSetup|AccountLogSetup[] $model
     * @return array|null
     */
    private function getAccountLogSetupRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getAccountLogSetupRecord($subModel);
            }
            return $result;

        } elseif ($model) {

            return [
                'date' => $model->date,
                'price' => $model->price,
                'tariff_id' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariff_period_id' => $model->tariff_period_id,
            ];

        } else {

            return null;

        }
    }

    /**
     * @param AccountLogPeriod|AccountLogPeriod[] $model
     * @return array|null
     */
    private function getAccountLogPeriodRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getAccountLogPeriodRecord($subModel);
            }
            return $result;

        } elseif ($model) {

            return [
                'date_from' => $model->date_from,
                'date_to' => $model->date_to,
                'price_per_period' => $model->period_price,
                'coefficient' => $model->coefficient,
                'price' => $model->price,
                'tariff_id' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariff_period_id' => $model->tariff_period_id,
            ];

        } else {

            return null;

        }
    }

    /**
     * @param AccountLogResource|AccountLogResource[] $model
     * @return array|null
     */
    private function getAccountLogResourceRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->getAccountLogResourceRecord($subModel);
            }
            return $result;

        } elseif ($model) {

            return [
                'date' => $model->date,
                'amount_use' => $model->amount_use,
                'amount_free' => $model->amount_free,
                'amount_overhead' => $model->amount_overhead,
                'price_per_unit' => $model->price_per_unit,
                'price' => $model->price,
                'tariff_id' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariff_period_id' => $model->tariff_period_id,
                'resource' => $this->getIdNameRecord($model->tariffResource->resource),
            ];

        } else {

            return null;

        }
    }

}