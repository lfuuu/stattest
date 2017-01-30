<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\behaviors\uu\SyncVmCollocation;
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
use app\exceptions\ModelValidationException;
use app\exceptions\web\NotImplementedHttpException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use Exception;
use InvalidArgumentException;
use LogicException;
use Yii;

class UuController extends ApiInternalController
{
    use IdNameRecordTrait;

    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-service-types", summary = "Список типов услуг", operationId = "Список типов услуг",
     *
     *   @SWG\Response(response = 200, description = "Список типов услуг (ВАТС, телефония, интернет и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
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
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-resources", summary = "Список ресурсов", operationId = "Список ресурсов",
     *
     *   @SWG\Response(response = 200, description = "Список ресурсов (дисковое пространство, абоненты, линии и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
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
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-periods", summary = "Список периодов", operationId = "Список периодов",
     *
     *   @SWG\Response(response = 200, description = "Список периодов (день, месяц, год и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
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
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-tariff-statuses", summary = "Список статусов тарифа", operationId = "Список статусов тарифа",
     *
     *   @SWG\Response(response = 200, description = "Список статусов тарифа (публичный, специальный, архивный и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
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
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-tariff-persons", summary = "Список для кого действует тариф", operationId = "Список для кого действует тариф",
     *
     *   @SWG\Response(response = 200, description = "Список для кого действует тариф (для всех, физиков, юриков)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
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
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-tariff-voip-groups", summary = "Список групп телефонии", operationId = "Список групп телефонии",
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
     *   @SWG\Property(property = "price_per_unit", type = "number", description = "Цена за превышение, ¤/ед."),
     *   @SWG\Property(property = "price_min", type = "number", description = "Мин. стоимость за месяц, ¤"),
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс (дисковое пространство, абоненты, линии и пр.)", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffPeriodRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID. Именно его надо указывать при создании услуги"),
     *   @SWG\Property(property = "price_setup", type = "number", description = "Цена подключения, ¤"),
     *   @SWG\Property(property = "price_per_period", type = "number", description = "Цена за период, ¤"),
     *   @SWG\Property(property = "price_min", type = "number", description = "Мин. стоимость ресурсов за период, ¤"),
     *   @SWG\Property(property = "period", type = "object", description = "Период абонентки (посуточно, помесячно)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "charge_period", type = "object", description = "Период списания (посуточно, помесячно)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff", type = "object", description = "Тариф", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "voipPackageMinuteRecord", type = "object",
     *   @SWG\Property(property = "destination", type = "string", description = "Направление"),
     *   @SWG\Property(property = "minute", type = "integer", description = "Количество предоплаченных минут"),
     * ),
     *
     * @SWG\Definition(definition = "voipPackagePriceRecord", type = "object",
     *   @SWG\Property(property = "destination", type = "string", description = "Направление"),
     *   @SWG\Property(property = "price", type = "number", description = "Цена"),
     * ),
     *
     * @SWG\Definition(definition = "voipPackagePricelistRecord", type = "object",
     *   @SWG\Property(property = "pricelist", type = "string", description = "Прайслист"),
     * ),
     *
     * @SWG\Definition(definition = "tariffRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "count_of_validity_period", type = "integer", description = "Кол-во периодов продления"),
     *   @SWG\Property(property = "is_autoprolongation", type = "integer", description = "Автопролонгация"),
     *   @SWG\Property(property = "is_charge_after_blocking", type = "integer", description = "Списывать после блокировки"),
     *   @SWG\Property(property = "is_charge_after_period", type = "integer", description = "Списывать в конце периода"),
     *   @SWG\Property(property = "is_include_vat", type = "integer", description = "Включая НДС"),
     *   @SWG\Property(property = "is_default", type = "integer", description = "По умолчанию. Если не указано, то 0"),
     *   @SWG\Property(property = "currency_id", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)"),
     *   @SWG\Property(property = "serviceType", type = "object", description = "Тип услуги (ВАТС, телефония, интернет и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_status", type = "object", description = "Статус (публичный, специальный, архивный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_person", type = "object", description = "Для кого действует тариф (для всех, физиков, юриков)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_resources", type = "array", description = "Ресурсы (дисковое пространство, абоненты, линии и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffResourceRecord")),
     *   @SWG\Property(property = "tariff_periods", type = "array", description = "Периоды (посуточно, помесячно и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "tarification_free_seconds", type = "integer", description = "Телефония. Бесплатно, секунд"),
     *   @SWG\Property(property = "tarification_interval_seconds", type = "integer", description = "Телефония. 'Интервал билингования, секунд"),
     *   @SWG\Property(property = "tarification_type", type = "integer", description = "Телефония. Тип округления. 1 - round, 2 - ceil"),
     *   @SWG\Property(property = "tarification_min_paid_seconds", type = "integer", description = "Телефония. Минимальная плата, секунд"),
     *   @SWG\Property(property = "voip_group", type = "object", description = "Телефония. Группа (местные, междугородние, международные и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_cities", type = "array", description = "Телефония. Города", @SWG\Items(ref = "#/definitions/idNameRecord")),
     *   @SWG\Property(property = "voip_package_minute", type = "array", description = "Телефония. Пакет. Предоплаченные минуты", @SWG\Items(ref = "#/definitions/voipPackageMinuteRecord")),
     *   @SWG\Property(property = "voip_package_price", type = "array", description = "Телефония. Пакет. Цена по направлениям", @SWG\Items(ref = "#/definitions/voipPackagePriceRecord")),
     *   @SWG\Property(property = "voip_package_pricelist", type = "array", description = "Телефония. Пакет. Прайслист", @SWG\Items(ref = "#/definitions/voipPackagePricelistRecord")),
     *   @SWG\Property(property = "default_packages", type = "array", description = "Дефолтные пакеты в тарифе", @SWG\Items(ref = "#/definitions/tariffRecord")),
     * ),
     *
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-tariffs", summary = "Список тарифов", operationId = "Список тарифов",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *   @SWG\Parameter(name = "parent_id", type = "integer", description = "ID родителя. Нужен для поиска совместимых пакетов", in = "query", default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "is_default", type = "integer", description = "По умолчанию (0 / 1)", in = "query", default = ""),
     *   @SWG\Parameter(name = "currency_id", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "country_id", type = "integer", description = "ID страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_status_id", type = "integer", description = "ID статуса (публичный, специальный, архивный и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_person_id", type = "integer", description = "ID для кого действует тариф (для всех, физиков, юриков)", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_group_id", type = "integer", description = "ID группы телефонии (местные, междугородние, международные и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_city_id", type = "integer", description = "ID города телефонии", in = "query", default = ""),
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
     * @param int $id
     * @param int $parent_id
     * @param int $service_type_id
     * @param int $country_id
     * @param int $currency_id
     * @param int $is_default
     * @param int $tariff_status_id
     * @param int $tariff_person_id
     * @param int $voip_group_id
     * @param int $voip_city_id
     * @return array
     */
    public function actionGetTariffs(
        $id = null,
        $parent_id = null,
        $service_type_id = null,
        $country_id = null,
        $currency_id = null,
        $is_default = 0,
        $tariff_status_id = null,
        $tariff_person_id = null,
        $voip_group_id = null,
        $voip_city_id = null
    ) {
        $id = (int)$id;
        $parent_id = (int)$parent_id;
        $service_type_id = (int)$service_type_id;
        $country_id = (int)$country_id;
        $is_default = (int)$is_default;
        $tariff_status_id = (int)$tariff_status_id;
        $tariff_person_id = (int)$tariff_person_id;
        $voip_group_id = (int)$voip_group_id;
        $voip_city_id = (int)$voip_city_id;

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
        !is_null($is_default) && $tariffQuery->andWhere([$tariffTableName . '.is_default' => (int)$is_default]);
        $tariff_status_id && $tariffQuery->andWhere([$tariffTableName . '.tariff_status_id' => (int)$tariff_status_id]);
        $tariff_person_id && $tariffQuery->andWhere([$tariffTableName . '.tariff_person_id' => (int)$tariff_person_id]);
        $voip_group_id && $tariffQuery->andWhere([$tariffTableName . '.voip_group_id' => (int)$voip_group_id]);

        if ($voip_city_id) {
            $tariffQuery->joinWith('voipCities');
            $tariffVoipCityTableName = TariffVoipCity::tableName();
            $tariffQuery->andWhere([$tariffVoipCityTableName . '.city_id' => $voip_city_id]);
        }

        $result = [];
        foreach ($tariffQuery->each() as $tariff) {
            /** @var Tariff $tariff */

            if ($tariff->service_type_id == ServiceType::ID_VOIP) {
                $defaultPackageRecords = $this->actionGetTariffs(
                    $id_tmp = null,
                    $parent_id_tmp = $tariff->id,
                    $service_type_id_tmp = ServiceType::ID_VOIP_PACKAGE,
                    $country_id_tmp = null,
                    $currency_id_tmp = null,
                    $is_default_tmp = 1,
                    $tariff_status_id_tmp = null,
                    $tariff_person_id_tmp = null,
                    $voip_group_id_tmp = null,
                    $voip_city_id_tmp = null
                );
            } else {
                $defaultPackageRecords = null;
            }

            $tariffRecord = $this->_getTariffRecord($tariff, $tariff->tariffPeriods);
            $tariffRecord['default_packages'] = $defaultPackageRecords;
            $result[] = $tariffRecord;
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "accountTariffLogRecord", type = "object",
     *   @SWG\Property(property = "tariff_period", type = "object", description = "Тариф/период. Если закрыто, то null", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
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
     *   @SWG\Property(property = "client_account_id", type = "integer", description = "ID ЛС"),
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
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-account-tariffs", summary = "Список услуг у ЛС", operationId = "Список услуг у ЛС",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона (кроме телефонии)", in = "query", default = ""),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города (только для телефонии)", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "query", default = ""),
     *   @SWG\Parameter(name = "prev_account_tariff_id", type = "integer", description = "ID основной услуги ЛС. Если список услуг пакета телефонии, то можно здесь указать ID услуги телефонии", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список услуг у ЛС",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/accountTariffRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */

    /**
     * @param Tariff $tariff
     * @param TariffPeriod|TariffPeriod[] $tariffPeriod
     * @return array
     */
    private function _getTariffRecord(Tariff $tariff, $tariffPeriod)
    {
        $package = $tariff->package;
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
            'tariff_person' => $this->getIdNameRecord($tariff->person),
            'tariff_resources' => $this->_getTariffResourceRecord($tariff->tariffResources),
            'tariff_periods' => $this->_getTariffPeriodRecord($tariffPeriod),
            'tarification_free_seconds' => $package ? $package->tarification_free_seconds : null,
            'tarification_interval_seconds' => $package ? $package->tarification_interval_seconds : null,
            'tarification_type' => $package ? $package->tarification_type : null,
            'tarification_min_paid_seconds' => $package ? $package->tarification_min_paid_seconds : null,
            'voip_group' => $this->getIdNameRecord($tariff->voipGroup),
            'voip_cities' => $this->getIdNameRecord($tariff->voipCities, 'city_id'),
            'voip_package_minute' => $this->_getVoipPackageMinuteRecord($tariff->packageMinutes),
            'voip_package_price' => $this->_getVoipPackagePriceRecord($tariff->packagePrices),
            'voip_package_pricelist' => $this->_getVoipPackagePricelistRecord($tariff->packagePricelists),
        ];
    }

    /**
     * @SWG\Definition(definition = "accountTariffLogLightRecord", type = "object",
     *   @SWG\Property(property = "tariff", type = "object", description = "Тариф/период", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "activate_past_date", type = "string", description = "Дата, с которой этот тариф был включен и сейчас действует. Всегда в прошлом. Если null - еще не включен (тогда см. activate_future_date) или уже выключен (deactivate_past_date). ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "activate_future_date", type = "string", description = "Дата, с которой этот тариф будет включен, и его можно отменить. Всегда в будущем. Если null - в будущем изменений не будет. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "deactivate_past_date", type = "string", description = "Дата, с которой этот тариф был выключен, и сейчас не действует. Всегда в прошлом. Если null - не был выключен. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "deactivate_future_date", type = "string", description = "Дата, с которой этот тариф будет выключен, и его можно отменить. Всегда в будущем. Если null - в будущем изменений не будет. ГГГГ-ММ-ДД"),
     * ),
     *
     * @SWG\Definition(definition = "accountTariffVoipRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID услуги"),
     *   @SWG\Property(property = "voip_number", type = "integer", description = "Если 4-5 символов - номер линии, если больше - номер телефона"),
     *   @SWG\Property(property = "voip_city", type = "object", description = "Город", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "is_cancelable", type = "boolean", description = "Можно ли отменить смену тарифа?"),
     *   @SWG\Property(property = "is_editable", type = "boolean", description = "Можно ли сменить тариф или отключить услугу?"),
     *   @SWG\Property(property = "log", type = "array", description = "Сокращенный лог тарифов (только текущий и будущий). По убыванию даты", @SWG\Items(ref = "#/definitions/accountTariffLogLightRecord")),
     *   @SWG\Property(property = "packages", type = "array", description = "Услуги пакета телефонии (если это телефония)", @SWG\Items(type = "array", @SWG\Items(ref = "#/definitions/accountTariffVoipRecord"))),
     * ),
     *
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-account-tariffs-voip", summary = "Услуги телефонии у ЛС", operationId = "Услуги телефонии у ЛС",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Услуги телефонии у ЛС",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/accountTariffVoipRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */

    /**
     * @param TariffResource|TariffResource[] $model
     * @return array|null
     */
    private function _getTariffResourceRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getTariffResourceRecord($subModel);
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
     * @SWG\Put(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/add-account-tariff", summary = "Добавить услугу ЛС", operationId = "Добавить услугу ЛС",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "tariff_period_id", type = "integer", description = "ID периода тарифа (например, 100 руб/мес, 1000 руб/год)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой этот тариф действует. ГГГГ-ММ-ДД. Если не указан, то с сегодня. Если с сегодня, то отменить нельзя - можно только закрыть с завтра", in = "formData", default = ""),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона (кроме телефонии)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города (только для телефонии)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "formData", default = ""),
     *   @SWG\Parameter(name = "comment", type = "string", description = "Комментарий", in = "formData", default = ""),
     *   @SWG\Parameter(name = "prev_account_tariff_id", type = "integer", description = "ID основной услуги ЛС. Если добавляется услуга пакета телефонии, то необходимо здесь указать ID услуги телефонии", in = "formData", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Услуга ЛС добавлена",
     *     @SWG\Schema(type = "integer", description = "ID")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */

    /**
     * @param TariffPeriod|TariffPeriod[] $model
     * @return array|null
     */
    private function _getTariffPeriodRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getTariffPeriodRecord($subModel);
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
            ];

        } else {

            return null;

        }
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/edit-account-tariff", summary = "Сменить тариф услуге ЛС", operationId = "Сменить тариф услуге ЛС",
     *   @SWG\Parameter(name = "account_tariff_ids[0]", type = "integer", description = "IDs услуг", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "account_tariff_ids[1]", type = "integer", description = "IDs услуг", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_period_id", type = "integer", description = "ID периода тарифа (например, 100 руб/мес, 1000 руб/год)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой этот тариф действует. ГГГГ-ММ-ДД. Если не указано - с завтра", in = "formData", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Тариф изменен",
     *     @SWG\Schema(type = "boolean", description = "true - успешно")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */

    /**
     * @param PackageMinute|PackageMinute[] $packageMinutes
     * @return array
     */
    private function _getVoipPackageMinuteRecord($packageMinutes)
    {
        if (!$packageMinutes) {
            return null;
        }

        if (is_array($packageMinutes)) {

            $result = [];
            foreach ($packageMinutes as $packageMinute) {
                $result[] = $this->_getVoipPackageMinuteRecord($packageMinute);
            }

            return $result;

        }

        return [
            'destination' => (string)$packageMinutes->destination,
            'minute' => $packageMinutes->minute,
        ];
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/close-account-tariff", summary = "Закрыть услугу ЛС", operationId = "Закрыть услугу ЛС",
     *   @SWG\Parameter(name = "account_tariff_ids[0]", type = "integer", description = "IDs услуг", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "account_tariff_ids[1]", type = "integer", description = "IDs услуг", in = "query", default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой услуга закрывается. ГГГГ-ММ-ДД. Если не указано - с завтра", in = "formData", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Услуга закрыта",
     *     @SWG\Schema(type = "boolean", description = "true - успешно")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */

    /**
     * @param PackagePrice|PackagePrice[] $packagePrices
     * @return array
     */
    private function _getVoipPackagePriceRecord($packagePrices)
    {
        if (!$packagePrices) {
            return null;
        }

        if (is_array($packagePrices)) {

            $result = [];
            foreach ($packagePrices as $packagePrice) {
                $result[] = $this->_getVoipPackagePriceRecord($packagePrice);
            }

            return $result;

        }

        return [
            'destination' => (string)$packagePrices->destination,
            'price' => $packagePrices->price,
        ];
    }

    /**
     * @SWG\Post(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/cancel-edit-account-tariff", summary = "Отменить последнюю смену тарифа (или закрытие) услуги ЛС", operationId = "Отменить последнюю смену тарифа (или закрытие) услуги ЛС",
     *   @SWG\Parameter(name = "account_tariff_ids[0]", type = "integer", description = "IDs услуг", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "account_tariff_ids[1]", type = "integer", description = "IDs услуг", in = "query", default = ""),
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
     * @param PackagePricelist|PackagePricelist[] $packagePricelists
     * @return array
     */
    private function _getVoipPackagePricelistRecord($packagePricelists)
    {
        if (!$packagePricelists) {
            return null;
        }

        if (is_array($packagePricelists)) {

            $result = [];
            foreach ($packagePricelists as $packagePricelist) {
                $result[] = $this->_getVoipPackagePricelistRecord($packagePricelist);
            }

            return $result;

        }

        return [
            'pricelist' => (string)$packagePricelists->pricelist,
        ];
    }

    /**
     * @param int $id
     * @param int $service_type_id
     * @param int $client_account_id
     * @param int $region_id
     * @param int $city_id
     * @param int $voip_number
     * @param int $prev_account_tariff_id
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
        $id = (int)$id;
        $service_type_id = (int)$service_type_id;
        $client_account_id = (int)$client_account_id;
        $region_id = (int)$region_id;
        $city_id = (int)$city_id;
        $prev_account_tariff_id = (int)$prev_account_tariff_id;

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
            throw new InvalidArgumentException('Необходимо указать фильтр id, service_type_id или client_account_id', AccountTariff::ERROR_CODE_SERVICE_TYPE);
        }

        $result = [];
        foreach ($accountTariffQuery->each() as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            $result[] = $this->_getAccountTariffRecord($accountTariff);
        }

        return $result;
    }

    /**
     * @param AccountTariff|AccountTariff[] $accountTariff
     * @return array
     */
    private function _getAccountTariffRecord($accountTariff)
    {
        if (!$accountTariff) {
            return null;
        }

        if (is_array($accountTariff)) {

            $result = [];
            foreach ($accountTariff as $subAccountTariff) {
                $result[] = $this->_getAccountTariffRecord($subAccountTariff);
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
            'next_account_tariffs' => $this->_getAccountTariffRecord($accountTariff->nextAccountTariffs),
            'comment' => $accountTariff->comment,
            'voip_number' => $accountTariff->voip_number,
            'account_tariff_logs' => $this->_getAccountTariffLogRecord($accountTariff->accountTariffLogs),
            'account_log_setups' => $this->_getAccountLogSetupRecord($accountTariff->accountLogSetups),
            'account_log_periods' => $this->_getAccountLogPeriodRecord($accountTariff->accountLogPeriods),
            'account_log_resources' => $this->_getAccountLogResourceRecord($accountTariff->accountLogResources),
        ];
    }

    /**
     * @param AccountTariffLog|AccountTariffLog[] $model
     * @return array|null
     */
    private function _getAccountTariffLogRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getAccountTariffLogRecord($subModel);
            }

            return $result;

        } elseif ($model) {

            return [
                'tariff_period' => $this->_getTariffPeriodRecord($model->tariffPeriod),
                'actual_from' => $model->actual_from,
            ];

        } else {

            return null;

        }
    }

    /**
     * @param AccountLogSetup|AccountLogSetup[] $model
     * @return array|null
     */
    private function _getAccountLogSetupRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getAccountLogSetupRecord($subModel);
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
    private function _getAccountLogPeriodRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getAccountLogPeriodRecord($subModel);
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
    private function _getAccountLogResourceRecord($model)
    {
        if (is_array($model)) {

            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getAccountLogResourceRecord($subModel);
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

    /**
     * @param int $client_account_id
     * @return array
     * @throws \InvalidArgumentException
     */
    public function actionGetAccountTariffsVoip(
        $client_account_id = null
    ) {
        $client_account_id = (int)$client_account_id;
        $service_type_id = ServiceType::ID_VOIP;

        $accountTariffQuery = AccountTariff::find();
        $accountTariffTableName = AccountTariff::tableName();
        $service_type_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.service_type_id' => (int)$service_type_id]);
        $client_account_id && $accountTariffQuery->andWhere([$accountTariffTableName . '.client_account_id' => (int)$client_account_id]);

        if (!$client_account_id) {
            throw new InvalidArgumentException('Необходимо указать фильтр client_account_id', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
        }

        $result = [];
        foreach ($accountTariffQuery->each() as $accountTariff) {
            $result[] = $this->_getAccountTariffVoipRecord($accountTariff);
        }

        return $result;
    }

    /**
     * Услуги
     *
     * @param AccountTariff $accountTariff
     * @return array
     */
    private function _getAccountTariffVoipRecord($accountTariff)
    {
        $record = [
            'id' => $accountTariff->id,
            'voip_number' => $accountTariff->voip_number,
            'voip_city' => $this->getIdNameRecord($accountTariff->city),
            'is_cancelable' => $accountTariff->isCancelable(), // Можно ли отменить смену тарифа?
            'is_editable' => (bool)$accountTariff->tariff_period_id, // Можно ли сменить тариф или отключить услугу?
            'log' => $this->_getAccountTariffLogLightRecord($accountTariff->accountTariffLogs),
            'packages' => null,
        ];

        $packages = $accountTariff->nextAccountTariffs;
        if ($packages) {
            $record['packages'] = [];
            foreach ($packages as $package) {
                $record['packages'][] = $this->_getAccountTariffVoipRecord($package);
            }
        }

        return $record;
    }

    /**
     * @param AccountTariffLog[] $models
     * @return array
     */
    private function _getAccountTariffLogLightRecord($models)
    {
        $result = [];

        /** @var AccountTariffLog $modelLast */
        $modelLast = array_shift($models);
        if (!$modelLast) {
            return $result;
        }

        /** @var AccountTariffLog $modelPrev */
        $modelPrev = array_shift($models);
        $isCancelable = $modelLast->actual_from > date(DateTimeZoneHelper::DATE_FORMAT);


        if ($modelLast->tariff_period_id) {

            if ($isCancelable) {

                // смена тарифа в будущем
                if ($modelPrev) {
                    // текущий тариф
                    $result[] = [
                        'tariff' => $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod),
                        'activate_past_date' => $modelPrev->actual_from,
                        'activate_future_date' => null,
                        'deactivate_past_date' => null,
                        'deactivate_future_date' => null,
                    ];
                }

                // будущий
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelLast->tariffPeriod->tariff, $modelLast->tariffPeriod),
                    'activate_past_date' => null,
                    'activate_future_date' => $modelLast->actual_from,
                    'deactivate_past_date' => null,
                    'deactivate_future_date' => null,
                ];

            } else {

                // смена тарифа в прошлом
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelLast->tariffPeriod->tariff, $modelLast->tariffPeriod),
                    'activate_past_date' => $modelLast->actual_from,
                    'activate_future_date' => null,
                    'deactivate_past_date' => null,
                    'deactivate_future_date' => null,
                ];

            }
        } else {

            if ($isCancelable) {

                // закрытие тарифа в будущем
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod),
                    'activate_past_date' => $modelPrev->actual_from,
                    'activate_future_date' => null,
                    'deactivate_past_date' => null,
                    'deactivate_future_date' => $modelLast->actual_from,
                ];

            } else {

                // закрытие тарифа в прошлом
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod),
                    'activate_past_date' => null,
                    'activate_future_date' => null,
                    'deactivate_past_date' => $modelLast->actual_from,
                    'deactivate_future_date' => null,
                ];

            }
        }


        if ($modelLast->tariff_period_id && $isCancelable) {

            // смена тарифа в будущем
            if (!$modelPrev) {
                return $result;
            }

            $result[] = [
                'tariff' => $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod),
                'activate_past_date' => $modelPrev->actual_from, // обычная смена тарифа в прошлом,
                'activate_future_date' => null,
                'deactivate_past_date' => null,
                'deactivate_future_date' => null,
            ];
        } elseif ($modelLast->tariff_period_id && $isCancelable) {

            // закрытие тарифа в будущем
            /** @var AccountTariffLog $modelPrev */
            $modelPrev = array_shift($modelPrevs);
            if (!$modelPrev) {
                return $result;
            }

            $result[0]['tariff'] = $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod);
            $result[0]['activate_past_date'] = $modelPrev->actual_from;
        }

        return $result;
    }

    /**
     * @return int
     * @throws Exception
     * @throws ModelValidationException
     */
    public function actionAddAccountTariff()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = Yii::$app->request->post();

            $accountTariff = new AccountTariff();
            $accountTariff->setAttributes($post);
            if (!$accountTariff->save()) {
                throw new ModelValidationException($accountTariff, $accountTariff->errorCode);
            }

            // записать в лог тарифа
            $accountTariffLog = new AccountTariffLog;
            $accountTariffLog->account_tariff_id = $accountTariff->id;
            $accountTariffLog->setAttributes($post);
            if (!$accountTariffLog->save()) {
                throw new ModelValidationException($accountTariffLog, $accountTariffLog->errorCode);
            }

            $transaction->commit();
            return $accountTariff->id;

        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $account_tariff_ids
     * @return int
     * @throws Exception
     */
    public function actionEditAccountTariff(array $account_tariff_ids = [])
    {
        $postData = Yii::$app->request->post();
        return $this->editAccountTariff(
            $account_tariff_ids,
            $postData['tariff_period_id'],
            isset($postData['actual_from']) ? $postData['actual_from'] : null
        );
    }

    /**
     * @param int[] $account_tariff_ids
     * @param int $tariff_period_id
     * @param string $actual_from
     * @return int
     * @throws Exception
     * @throws ModelValidationException
     */
    public function editAccountTariff($account_tariff_ids, $tariff_period_id, $actual_from)
    {
        if (!$account_tariff_ids) {
            throw new InvalidArgumentException('Не указан обязательный параметр account_tariff_ids', AccountTariff::ERROR_CODE_USAGE_EMPTY);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {

            foreach ($account_tariff_ids as $account_tariff_id) {

                $accountTariff = AccountTariff::findOne(['id' => (int)$account_tariff_id]);
                if (!$accountTariff) {
                    throw new InvalidArgumentException('Услуга с таким идентификатором не найдена ' . $account_tariff_id, AccountTariff::ERROR_CODE_USAGE_EMPTY);
                }

                // у услуги сменить кэш тарифа
                $accountTariff->tariff_period_id = $tariff_period_id;
                if (!$accountTariff->save()) {
                    throw new ModelValidationException($accountTariff, $accountTariff->errorCode);
                }

                // записать в лог тарифа
                $accountTariffLog = new AccountTariffLog;
                $accountTariffLog->account_tariff_id = $accountTariff->id;
                $accountTariffLog->tariff_period_id = $tariff_period_id;
                $accountTariffLog->actual_from = $actual_from;
                if (!$accountTariffLog->save()) {
                    throw new ModelValidationException($accountTariffLog, $accountTariffLog->errorCode);
                }
            }

            $transaction->commit();
            return true;

        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $account_tariff_ids
     * @return int
     * @throws Exception
     */
    public function actionCloseAccountTariff(array $account_tariff_ids = [])
    {
        $postData = Yii::$app->request->post();
        return $this->editAccountTariff(
            $account_tariff_ids,
            null,
            isset($postData['actual_from']) ? $postData['actual_from'] : null
        );
    }

    /**
     * @param array $account_tariff_ids
     * @return int
     * @throws \yii\db\StaleObjectException
     * @throws \Exception
     * @throws \app\exceptions\ModelValidationException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function actionCancelEditAccountTariff(array $account_tariff_ids = [])
    {
        if (!$account_tariff_ids) {
            throw new InvalidArgumentException('Не указан обязательный параметр account_tariff_ids', AccountTariff::ERROR_CODE_USAGE_EMPTY);
        }

        foreach ($account_tariff_ids as $account_tariff_id) {

            $account_tariff_id = trim($account_tariff_id);
            $accountTariff = AccountTariff::findOne(['id' => (int)$account_tariff_id]);
            if (!$accountTariff) {
                throw new InvalidArgumentException('Услуга с таким идентификатором не найдена', AccountTariff::ERROR_CODE_USAGE_EMPTY);
            }

            if (!$accountTariff->isCancelable()) {
                throw new LogicException('Нельзя отменить уже примененный тариф', AccountTariff::ERROR_CODE_USAGE_CANCELABLE);
            }

            // лог тарифов
            $accountTariffLogs = $accountTariff->accountTariffLogs;

            // отменяемый тариф
            /** @var AccountTariffLog $accountTariffLogCancelled */
            $accountTariffLogCancelled = array_shift($accountTariffLogs);
            if (!$accountTariff->isCancelable()) {
                throw new LogicException('Нельзя отменить уже примененный тариф', AccountTariff::ERROR_CODE_USAGE_CANCELABLE);
            }

            // отменить (удалить) последний тариф
            if (!$accountTariffLogCancelled->delete()) {
                throw new ModelValidationException($accountTariffLogCancelled, $accountTariffLogCancelled->errorCode);
            }

            if (!count($accountTariffLogs)) {

                // услуга еще даже не начинала действовать, текущего тарифа нет - удалить услугу полностью
                if (!$accountTariff->delete()) {
                    throw new ModelValidationException($accountTariff, $accountTariff->errorCode);
                }
            } else {

                // предпоследний тариф становится текущим
                /** @var AccountTariffLog $accountTariffLogActual */
                $accountTariffLogActual = array_shift($accountTariffLogs);

                // у услуги сменить кэш тарифа
                $accountTariff->tariff_period_id = $accountTariffLogActual->tariff_period_id;
                if (!$accountTariff->save()) {
                    throw new ModelValidationException($accountTariff, $accountTariff->errorCode);
                }
            }
        }

        return true;
    }

    /**
     * @SWG\Definition(definition = "vmCollocationRecord", type = "object",
     *   @SWG\Property(property="vm_user_id", type="string|null", description="ID юзера в VM manager (обычно не нужен)"),
     *   @SWG\Property(property="vm_user_login", type="string|null", description="Логин юзера в VM manager"),
     *   @SWG\Property(property="vm_user_password", type="string|null", description="Постоянный пароль юзера в VM manager (обычно не нужен)"),
     * ),
     *
     * @SWG\Get(tags = {"Универсальные тарифы и услуги"}, path = "/internal/uu/get-vm-collocation-info", summary = "Информация о VM collocation ЛС", operationId = "Информация о VM collocation ЛС",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Информация о VM collocation ЛС",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/vmCollocationRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $client_account_id
     * @return array
     */
    public function actionGetVmCollocationInfo($client_account_id = 0)
    {
        $client_account_id = (int)$client_account_id;
        if (!$client_account_id) {
            throw new InvalidArgumentException('Не указан client_account_id');
        }

        $account = ClientAccount::findOne(['id' => $client_account_id]);
        if (!$account) {
            throw new InvalidArgumentException('Несуществующий client_account_id ' . $client_account_id);
        }

        $syncVmCollocation = (new SyncVmCollocation);
        return [
            'vm_user_id' => $vm_user_id = $syncVmCollocation->getVmUserInfo($account, SyncVmCollocation::CLIENT_ACCOUNT_OPTION_VM_ELID),
            'vm_user_login' => $vm_user_id ? ('client_' . $client_account_id) : null,
            'vm_user_password' => $syncVmCollocation->getVmUserInfo($account, SyncVmCollocation::CLIENT_ACCOUNT_OPTION_VM_PASSWORD),
        ];
    }
}