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
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор"),
     *   @SWG\Property(property = "amount", type = "number", description = "Включено, ед."),
     *   @SWG\Property(property = "pricePerUnit", type = "number", description = "Цена за превышение, у.е./ед."),
     *   @SWG\Property(property = "priceMin", type = "number", description = "Мин. стоимость за месяц, у.е."),
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс (дисковое пространство, абоненты, линии и пр.)", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffPeriodRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор. Именно его надо указывать при создании услуги"),
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
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-tariffs", summary = "Список тарифов", operationId = "Список тарифов",
     *   @SWG\Parameter(name = "id", type = "integer", description = "Идентификатор", in = "query"),
     *   @SWG\Parameter(name = "parentId", type = "integer", description = "Идентификатор родителя. Нужен для поиска совместимых пакетов", in = "query"),
     *   @SWG\Parameter(name = "serviceTypeId", type = "integer", description = "Идентификатор типа услуги (ВАТС, телефония, интернет и пр.)", in = "query", required = true),
     *   @SWG\Parameter(name = "isDefault", type = "integer", description = "По умолчанию (0 / 1)", in = "query"),
     *   @SWG\Parameter(name = "currencyId", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)", in = "query"),
     *   @SWG\Parameter(name = "countryId", type = "integer", description = "Идентификатор страны", in = "query"),
     *   @SWG\Parameter(name = "tariffStatusId", type = "integer", description = "Идентификатор статуса (публичный, специальный, архивный и пр.)", in = "query"),
     *   @SWG\Parameter(name = "tariffPersonId", type = "integer", description = "Идентификатор для кого действует тариф (для всех, физиков, юриков)", in = "query"),
     *   @SWG\Parameter(name = "voipTarificateId", type = "integer", description = "Идентификатор типа тарификации телефонии (посекундный, поминутный и пр.)", in = "query"),
     *   @SWG\Parameter(name = "voipGroupId", type = "integer", description = "Идентификатор группы телефонии (местные, междугородние, международные и пр.)", in = "query"),
     *   @SWG\Parameter(name = "voipCityId", type = "integer", description = "Идентификатор города телефонии", in = "query"),
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
        $parentId = null,
        $serviceTypeId = null,
        $countryId = null,
        $currencyId = null,
        $isDefault = null,
        $tariffStatusId = null,
        $tariffPersonId = null,
        $voipTarificateId = null,
        $voipGroupId = null,
        $voipCityId = null
    ) {
        if ($parentId) {
            // передан родительский тариф (предполагается, что телефонии), надо найти пакеты
            /** @var Tariff $tariff */
            $tariff = Tariff::find()->where(['id' => (int)$parentId])->one();
            if (!$tariff) {
                return [];
            }
            $serviceTypeId = ServiceType::ID_VOIP_PACKAGE; // других пакетов пока все равно нет
            !$countryId && $countryId = $tariff->country_id;
            !$currencyId && $currencyId = $tariff->currency_id;
            !$voipCityId && $voipCityId = array_keys($tariff->voipCities);
            unset($tariff);
        }

        $tariffQuery = Tariff::find();
        $tariffTableName = Tariff::tableName();
        $id && $tariffQuery->andWhere([$tariffTableName . '.id' => (int)$id]);
        $serviceTypeId && $tariffQuery->andWhere([$tariffTableName . '.service_type_id' => (int)$serviceTypeId]);
        $countryId && $tariffQuery->andWhere([$tariffTableName . '.country_id' => (int)$countryId]);
        $currencyId && $tariffQuery->andWhere([$tariffTableName . '.currency_id' => $currencyId]);
        $isDefault && $tariffQuery->andWhere([$tariffTableName . '.is_default' => (int)$isDefault]);
        $tariffStatusId && $tariffQuery->andWhere([$tariffTableName . '.tariff_status_id' => (int)$tariffStatusId]);
        $tariffPersonId && $tariffQuery->andWhere([$tariffTableName . '.tariff_person_id' => (int)$tariffPersonId]);
        $voipTarificateId && $tariffQuery->andWhere([$tariffTableName . '.voip_tarificate_id' => (int)$voipTarificateId]);
        $voipGroupId && $tariffQuery->andWhere([$tariffTableName . '.voip_group_id' => (int)$voipGroupId]);

        if ($voipCityId) {
            $tariffQuery->joinWith('voipCities');
            $tariffVoipCityTableName = TariffVoipCity::tableName();
            $tariffQuery->andWhere([$tariffVoipCityTableName . '.city_id' => $voipCityId]);
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
     *   @SWG\Property(property = "tariffId", type = "integer", description = "Идентификатор тарифа. Если закрыто, то null"),
     *   @SWG\Property(property = "tariffPeriodId", type = "integer", description = "Идентификатор периода тарифа. Если закрыто, то null"),
     *   @SWG\Property(property = "actualFrom", type = "string", description = "Дата, с которой этот тариф действует"),
     * ),
     *
     * @SWG\Definition(definition = "accountLogSetupRecord", type = "object",
     *   @SWG\Property(property = "date", type = "string", description = "Дата списания"),
     *   @SWG\Property(property = "price", type = "number", description = "Стоимость"),
     *   @SWG\Property(property = "tariffId", type = "integer", description = "Идентификатор тарифа"),
     *   @SWG\Property(property = "tariffPeriodId", type = "integer", description = "Идентификатор периода тарифа"),
     * ),
     *
     * @SWG\Definition(definition = "accountLogPeriodRecord", type = "object",
     *   @SWG\Property(property = "dateFrom", type = "string", description = "Дата начала диапазона списания"),
     *   @SWG\Property(property = "dateTo", type = "string", description = "Дата окончания диапазона списания"),
     *   @SWG\Property(property = "pricePerPeriod", type = "number", description = "Цена за полный период"),
     *   @SWG\Property(property = "coefficient", type = "number", description = "Коэффициент неполного периода"),
     *   @SWG\Property(property = "price", type = "number", description = "Стоимость"),
     *   @SWG\Property(property = "tariffId", type = "integer", description = "Идентификатор тарифа"),
     *   @SWG\Property(property = "tariffPeriodId", type = "integer", description = "Идентификатор периода тарифа"),
     * ),
     *
     * @SWG\Definition(definition = "accountLogResourcesRecord", type = "object",
     *   @SWG\Property(property = "date", type = "string", description = "Дата списания"),
     *   @SWG\Property(property = "amountUse", type = "number", description = "Потрачено ресурса"),
     *   @SWG\Property(property = "amountFree", type = "number", description = "Доступно ресурса бесплатно"),
     *   @SWG\Property(property = "amountOverhead", type = "number", description = "Платное превышение ресурса"),
     *   @SWG\Property(property = "pricePerUnit", type = "number", description = "Цена единицы ресурса"),
     *   @SWG\Property(property = "price", type = "number", description = "Стоимость"),
     *   @SWG\Property(property = "tariffId", type = "integer", description = "Идентификатор тарифа"),
     *   @SWG\Property(property = "tariffPeriodId", type = "integer", description = "Идентификатор периода тарифа"),
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс (дисковое пространство, абоненты, линии и пр.)", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffAccountRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор"),
     *   @SWG\Property(property = "clientAccountId", type = "integer", description = "Идентификатор аккаунта клиента"),
     *   @SWG\Property(property = "serviceType", type = "object", description = "Тип услуги (ВАТС, телефония, интернет и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "region", type = "object", description = "Регион (кроме телефонии)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "city", type = "object", description = "Город (только для телефонии)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "prevAccountTariffId", type = "integer", description = "Идентификатор основной услуги телефонии (если это пакет телефонии)"),
     *   @SWG\Property(property = "nextAccountTariffId", type = "array", description = "Идентификаторы услуги пакета телефонии (если это телефония)", @SWG\Items(type = "integer")),
     *   @SWG\Property(property = "comment", type = "string", description = "Комментарий"),
     *   @SWG\Property(property = "voipNumber", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона"),
     *   @SWG\Property(property = "accountTariffLogs", type = "array", description = "Лог тарифов", @SWG\Items(ref = "#/definitions/accountTariffLogRecord")),
     *   @SWG\Property(property = "accountLogSetups", type = "array", description = "Транзакции за подключение", @SWG\Items(ref = "#/definitions/accountLogSetupRecord")),
     *   @SWG\Property(property = "accountLogPeriods", type = "array", description = "Транзакции за абонентскую плату", @SWG\Items(ref = "#/definitions/accountLogPeriodRecord")),
     *   @SWG\Property(property = "accountLogResources", type = "array", description = "Транзакции за ресурсы", @SWG\Items(ref = "#/definitions/accountLogResourcesRecord")),
     * ),
     *
     * @SWG\Get(tags = {"Универсальные тарифы"}, path = "/internal/uu/get-account-tariffs", summary = "Список услуг у клиента", operationId = "Список услуг у клиента",
     *   @SWG\Parameter(name = "id", type = "integer", description = "Идентификатор", in = "query"),
     *   @SWG\Parameter(name = "clientAccountId", type = "integer", description = "Идентификатор аккаунта клиента", in = "query"),
     *   @SWG\Parameter(name = "serviceTypeId", type = "integer", description = "Идентификатор типа услуги (ВАТС, телефония, интернет и пр.)", in = "query"),
     *   @SWG\Parameter(name = "regionId", type = "integer", description = "Идентификатор региона (кроме телефонии)", in = "query"),
     *   @SWG\Parameter(name = "cityId", type = "integer", description = "Идентификатор города (только для телефонии)", in = "query"),
     *   @SWG\Parameter(name = "voipNumber", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "query"),
     *
     *   @SWG\Response(response = 200, description = "Список услуг у клиента",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/tariffAccountRecord"))
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
        $serviceTypeId = null,
        $clientAccountId = null,
        $regionId = null,
        $cityId = null,
        $voipNumber = null
    ) {
        $accountTariffQuery = AccountTariff::find();
        $accountTariffTableName = AccountTariff::tableName();
        $id && $accountTariffQuery->andWhere([$accountTariffTableName . '.id' => (int)$id]);
        $serviceTypeId && $accountTariffQuery->andWhere([$accountTariffTableName . '.service_type_id' => (int)$serviceTypeId]);
        $clientAccountId && $accountTariffQuery->andWhere([$accountTariffTableName . '.client_account_id' => (int)$clientAccountId]);
        $regionId && $accountTariffQuery->andWhere([$accountTariffTableName . '.region_id' => (int)$regionId]);
        $cityId && $accountTariffQuery->andWhere([$accountTariffTableName . '.city_id' => (int)$cityId]);
        $voipNumber && $accountTariffQuery->andWhere([$accountTariffTableName . '.voip_number' => $voipNumber]);

        if (!$id && !$serviceTypeId && !$clientAccountId) {
            throw new InvalidArgumentException('Необходимо указать фильтр id, serviceTypeId или clientAccountId');
        }

        $result = [];
        foreach ($accountTariffQuery->each() as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            $result[] = $this->getAccountTariffRecord($accountTariff);
        }

        return $result;
    }

    /**
     * @SWG\Put(tags = {"Универсальные тарифы"}, path = "/internal/uu/add-account-tariff", summary = "Добавить услугу клиенту", operationId = "Добавить услугу клиенту",
     *   @SWG\Parameter(name = "clientAccountId", type = "integer", description = "Идентификатор аккаунта клиента", in = "formData", required = true),
     *   @SWG\Parameter(name = "serviceTypeId", type = "integer", description = "Идентификатор типа услуги (ВАТС, телефония, интернет и пр.)", in = "formData", required = true),
     *   @SWG\Parameter(name = "tariffPeriodId", type = "integer", description = "Идентификатор периода тарифа (не сам тариф!)", in = "formData", required = true),
     *   @SWG\Parameter(name = "actualFrom", type = "string", description = "Дата, с которой этот тариф действует", in = "formData", required = true),
     *   @SWG\Parameter(name = "regionId", type = "integer", description = "Идентификатор региона (кроме телефонии)", in = "formData"),
     *   @SWG\Parameter(name = "cityId", type = "integer", description = "Идентификатор города (только для телефонии)", in = "formData"),
     *   @SWG\Parameter(name = "voipNumber", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "formData"),
     *   @SWG\Parameter(name = "comment", type = "string", description = "Комментарий", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Услуга клиенту добавлена",
     *     @SWG\Schema(type = "integer", description = "Идентификатор")
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
        $postData = Yii::$app->request->post();

        $accountTariff = new AccountTariff();
        isset($postData['clientAccountId']) && $accountTariff->client_account_id = (int)$postData['clientAccountId'];
        isset($postData['serviceTypeId']) && $accountTariff->service_type_id = (int)$postData['serviceTypeId'];
        isset($postData['regionId']) && $accountTariff->region_id = (int)$postData['regionId'];
        isset($postData['cityId']) && $accountTariff->city_id = (int)$postData['cityId'];
        isset($postData['voipNumber']) && $accountTariff->voip_number = $postData['voipNumber'];
        isset($postData['comment']) && $accountTariff->comment = $postData['comment'];
        isset($postData['tariffPeriodId']) && $accountTariff->tariff_period_id = $postData['tariffPeriodId'];

        if (!$accountTariff->save()) {
            throw new ExceptionValidationForm($accountTariff);
        }

        // записать в лог тарифа
        $accountTariffLog = new AccountTariffLog;
        $accountTariffLog->account_tariff_id = $accountTariff->id;
        isset($postData['tariffPeriodId']) && $accountTariffLog->tariff_period_id = $postData['tariffPeriodId'];
        isset($postData['actualFrom']) && $accountTariffLog->actual_from = $postData['actualFrom'];
        if ($accountTariffLog->save()) {
            return $accountTariff->id;
        } else {
            throw new ExceptionValidationForm($accountTariffLog);
        }
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/edit-account-tariff", summary = "Сменить тариф услуге клиента", operationId = "Сменить тариф услуге клиента",
     *   @SWG\Parameter(name = "accountTariffId", type = "integer", description = "Идентификатор услуги", in = "query", required = true),
     *   @SWG\Parameter(name = "tariffPeriodId", type = "integer", description = "Идентификатор периода тарифа (не сам тариф!)", in = "formData", required = true),
     *   @SWG\Parameter(name = "actualFrom", type = "string", description = "Дата, с которой этот тариф действует. Если не указано - с завтра", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Тариф изменен",
     *     @SWG\Schema(type = "integer", description = "Идентификатор записи в логе тарифов")
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
    public function actionEditAccountTariff($accountTariffId = null)
    {
        $postData = Yii::$app->request->post();
        return $this->editAccountTariff(
            $accountTariffId,
            $postData['tariffPeriodId'],
            isset($postData['actualFrom']) ? $postData['actualFrom'] : null
        );
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы"}, path = "/internal/uu/close-account-tariff", summary = "Закрыть услугу клиента", operationId = "Закрыть услугу клиента",
     *   @SWG\Parameter(name = "accountTariffId", type = "integer", description = "Идентификатор услуги", in = "query", required = true),
     *   @SWG\Parameter(name = "actualFrom", type = "string", description = "Дата, с которой услуга закрывается. Если не указано - с завтра", in = "formData"),
     *
     *   @SWG\Response(response = 200, description = "Услуга закрыта",
     *     @SWG\Schema(type = "integer", description = "Идентификатор записи в логе тарифов")
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
    public function actionCloseAccountTariff($accountTariffId = null)
    {
        $postData = Yii::$app->request->post();
        return $this->editAccountTariff(
            $accountTariffId,
            null,
            isset($postData['actualFrom']) ? $postData['actualFrom'] : null
        );
    }

    /**
     * @param $accountTariffId
     * @param $tariffPeriodId
     * @param $actualFrom
     * @return int
     * @throws ExceptionValidationForm
     */
    public function editAccountTariff($accountTariffId, $tariffPeriodId, $actualFrom)
    {
        if (!$accountTariffId) {
            throw new InvalidArgumentException('Не указан обязательный параметр');
        }
        $accountTariff = AccountTariff::findOne(['id' => (int)$accountTariffId]);
        if (!$accountTariff) {
            throw new InvalidArgumentException('Услуга с таким идентификатором не найдена');
        }

        // записать в лог тарифа
        $accountTariffLog = new AccountTariffLog;
        $accountTariffLog->account_tariff_id = $accountTariff->id;
        $accountTariffLog->tariff_period_id = $tariffPeriodId;
        $accountTariffLog->actual_from = $actualFrom ?: (new \DateTimeImmutable())->modify('tomorrow')->format('Y-m-d');
        if ($accountTariffLog->save()) {
            return $accountTariffLog->id;
        } else {
            throw new ExceptionValidationForm($accountTariffLog);
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
     * @param AccountTariff $accountTariff
     * @return array
     */
    private function getAccountTariffRecord(AccountTariff $accountTariff)
    {
        return [
            'id' => $accountTariff->id,
            'clientAccountId' => $accountTariff->client_account_id,
            'serviceType' => $this->getIdNameRecord($accountTariff->serviceType),
            'region' => $this->getIdNameRecord($accountTariff->region),
            'city' => $this->getIdNameRecord($accountTariff->city),
            'prevAccountTariffId' => $accountTariff->prev_account_tariff_id,
            'nextAccountTariffId' => array_keys($accountTariff->nextAccountTariffs),
            'comment' => $accountTariff->comment,
            'voipNumber' => $accountTariff->voip_number,
            'accountTariffLogs' => $this->getAccountTariffLogRecord($accountTariff->accountTariffLogs),
            'accountLogSetups' => $this->getAccountLogSetupRecord($accountTariff->accountLogSetups),
            'accountLogPeriods' => $this->getAccountLogPeriodRecord($accountTariff->accountLogPeriods),
            'accountLogResources' => $this->getAccountLogResourceRecord($accountTariff->accountLogResources),
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

    /**
     * @param AccountTariffLog|AccountTariffLog[] $model
     * @return array
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
                'tariffId' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariffPeriodId' => $model->tariff_period_id,
                'actualFrom' => $model->actual_from,
            ];

        } else {

            return [];

        }
    }

    /**
     * @param AccountLogSetup|AccountLogSetup[] $model
     * @return array
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
                'tariffId' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariffPeriodId' => $model->tariff_period_id,
            ];

        } else {

            return [];

        }
    }

    /**
     * @param AccountLogPeriod|AccountLogPeriod[] $model
     * @return array
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
                'dateFrom' => $model->date_from,
                'dateTo' => $model->date_to,
                'pricePerPeriod' => $model->period_price,
                'coefficient' => $model->coefficient,
                'price' => $model->price,
                'tariffId' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariffPeriodId' => $model->tariff_period_id,
            ];

        } else {

            return [];

        }
    }

    /**
     * @param AccountLogResource|AccountLogResource[] $model
     * @return array
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
                'amountUse' => $model->amount_use,
                'amountFree' => $model->amount_free,
                'amountOverhead' => $model->amount_overhead,
                'pricePerUnit' => $model->price_per_unit,
                'price' => $model->price,
                'tariffId' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariffPeriodId' => $model->tariff_period_id,
                'resource' => $this->getIdNameRecord($model->tariffResource->resource),
            ];

        } else {

            return [];

        }
    }

}