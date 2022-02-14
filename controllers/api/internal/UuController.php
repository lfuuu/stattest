<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\classes\helpers\DependecyHelper;
use app\exceptions\ModelValidationException;
use app\exceptions\web\NotImplementedHttpException;
use app\helpers\DateTimeZoneHelper;
use app\helpers\Semaphore;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\EventQueue;
use app\models\Number;
use app\models\Trouble;
use app\models\TroubleRoistat;
use app\models\TroubleRoistatStore;
use app\models\User;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use app\modules\nnp\models\PackagePricelistNnp;
use app\modules\uu\classes\SyncVps;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\filter\TariffFilter;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Bill;
use app\modules\uu\models\billing_uu\Pricelist;
use app\modules\uu\models\Period;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffStatus;
use app\modules\uu\models\Tag;
use app\modules\uu\models\TariffVoipGroup;
use app\modules\uu\Module;
use app\modules\async\Module as asyncModule;
use app\modules\nnp\models\NdcType;
use DateTimeZone;
use Exception;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidParamException;
use yii\caching\TagDependency;
use yii\web\HttpException;

class UuController extends ApiInternalController
{
    const DEFAULT_LIMIT = 50;
    const MAX_LIMIT = 100;

    use IdNameRecordTrait;

    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Definition(definition = "GetCalltrackingLogs", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "account_tariff_id", type = "integer", description = "Услуга"),
     *   @SWG\Property(property = "voip_number", type = "integer", description = "Телефонный номер"),
     *   @SWG\Property(property = "start_dt", type = "string", description = "Время начала аренды номера"),
     *   @SWG\Property(property = "disconnect_dt", type = "string", description = "Время разрыва коннекта с юзер-агентом"),
     *   @SWG\Property(property = "stop_dt", type = "string", description = "Время окончания аренды номера"),
     *   @SWG\Property(property = "user_agent", type = "string", description = "User agent"),
     *   @SWG\Property(property = "ip", type = "string", description = "IP"),
     *   @SWG\Property(property = "url", type = "string", description = "URL"),
     *   @SWG\Property(property = "referrer", type = "string", description = "Referrer"),
     * ),
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-calltracking-logs", summary = "Список Calltracking логов", operationId = "GetCalltrackingLogs",
     *   @SWG\Parameter(name = "account_tariff_id", type = "integer", description = "Услуга", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "start_dt", type = "string", description = "Время начала аренды номера", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "stop_dt", type = "string", description = "Время окончания аренды номера", in = "query", required = true, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список Calltracking логов",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/GetCalltrackingLogs"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $account_tariff_id
     * @param string $start_dt
     * @param string $stop_dt
     *
     * @return array
     */
    public function actionGetCalltrackingLogs($account_tariff_id, $start_dt, $stop_dt)
    {
        $result = [];

        if ($account_tariff_id) {
            $logs = \app\modules\callTracking\models\Log::find()
                ->where(['account_tariff_id' => $account_tariff_id])
                ->andWhere('start_dt :: date >= :start_dt', ['start_dt' => $start_dt])
                ->andWhere('stop_dt :: date <= :stop_dt', ['stop_dt' => $stop_dt]);

            /** @var \app\modules\callTracking\models\Log $log */
            foreach ($logs->each() as $log) {
                $result[] = $log->getAttributes();
            }
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-service-types", summary = "Список типов услуг", operationId = "GetServiceTypes",
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
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "resourceRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "unit", type = "string", description = "Ед. изм."),
     *   @SWG\Property(property = "is_number", type = "boolean", description = "true - numeric, false - boolean"),
     *   @SWG\Property(property = "min_value", type = "string", description = "Минимум, ед."),
     *   @SWG\Property(property = "max_value", type = "string", description = "Максимум, ед."),
     *   @SWG\Property(property = "is_option", type = "boolean", description = "Опция? Иначе ресурс"),
     *   @SWG\Property(property = "service_type", type = "object", description = "Тип услуги", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-resources", summary = "Список ресурсов", operationId = "GetResources",
     *
     *   @SWG\Response(response = 200, description = "Список ресурсов (дисковое пространство, абоненты, линии и пр.)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/resourceRecord"))
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
        $query = ResourceModel::find();
        $result = [];
        /** @var ResourceModel $model */
        foreach ($query->each() as $model) {
            $result[] = $this->_getResourceRecord($model);
        }

        return $result;
    }

    /**
     * @param ResourceModel $model
     * @return array
     */
    private function _getResourceRecord($model)
    {
        if (!$model) {
            return [];
        }

        return [
            'id' => $model->id,
            'name' => $model->name,
            'unit' => $model->unit,
            'is_number' => $model->isNumber(),
            'min_value' => $model->min_value,
            'max_value' => $model->max_value,
            'is_option' => $model->isOption(),
            'service_type' => $this->_getIdNameRecord($model->serviceType),
        ];
    }

    /**
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-periods", summary = "Список периодов", operationId = "GetPeriods",
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
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-tariff-statuses", summary = "Список статусов тарифа", operationId = "GetTariffStatuses",
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
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-tariff-persons", summary = "Список для кого действует тариф", operationId = "GetTariffPersons",
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
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-tariff-tags", summary = "Список тегов", operationId = "GetTariffTags",
     *
     *   @SWG\Response(response = 200, description = "Список тегов (хит продаж)",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/idNameRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @return array
     */
    public function actionGetTariffTags()
    {
        $query = Tag::find();
        $result = [];
        foreach ($query->each() as $model) {
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-tariff-voip-groups", summary = "Список групп телефонии", operationId = "GetTariffVoipGroups",
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
            $result[] = $this->_getIdNameRecord($model);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "tariffResourceRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "is_checkable", type = "boolean", description = "false - отобразить число amount, true - отобразить галочку is_checked"),
     *   @SWG\Property(property = "is_checked", type = "boolean", description = "Включена или выключена галочка. Имеет смысл только при is_checkable=true"),
     *   @SWG\Property(property = "amount", type = "number", description = "Включено, ед. Имеет смысл только при is_checkable=false"),
     *   @SWG\Property(property = "price_per_unit", type = "number", description = "Цена за превышение, ¤/ед."),
     *   @SWG\Property(property = "price_min", type = "number", description = "Мин. стоимость за месяц, ¤"),
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс (дисковое пространство, абоненты, линии и пр.)", ref = "#/definitions/resourceRecord"),
     * ),
     *
     * @SWG\Definition(definition = "tariffPeriodRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID. Именно его надо указывать при создании услуги"),
     *   @SWG\Property(property = "price_setup", type = "number", description = "Цена подключения, ¤"),
     *   @SWG\Property(property = "price_per_period", type = "number", description = "Цена за месяц, ¤"),
     *   @SWG\Property(property = "price_per_charge_period", type = "number", description = "Примерная цена за период списания, ¤"),
     *   @SWG\Property(property = "price_min", type = "number", description = "Мин. стоимость ресурсов за месяц, ¤"),
     *   @SWG\Property(property = "charge_period", type = "object", description = "Период списания (день, месяц, год)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff", type = "object", description = "Тариф", ref = "#/definitions/idNameRecord"),
     * ),
     *
     * @SWG\Definition(definition = "voipPackageMinuteRecord", type = "object",
     *   @SWG\Property(property = "destination", type = "string", description = "Направление"),
     *   @SWG\Property(property = "minute", type = "integer", description = "Количество предоплаченных минут"),
     *   @SWG\Property(property = "spent_seconds", type = "integer", description = "Количество потраченных секунд"),
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
     *   @SWG\Property(property = "count_of_validity_period", type = "integer", description = "Кол-во продлений"),
     *   @SWG\Property(property = "is_autoprolongation", type = "integer", description = "Автопролонгация"),
     *   @SWG\Property(property = "is_charge_after_blocking", type = "integer", description = "Списывать после блокировки"),
     *   @SWG\Property(property = "is_include_vat", type = "integer", description = "Включая НДС"),
     *   @SWG\Property(property = "is_default", type = "integer", description = "0 - только не по умолчанию, 1 - только по умолчанию, не указано - все"),
     *   @SWG\Property(property = "is_postpaid", type = "integer", description = "0 - только предоплата, 1 - только постоплата, не указано - все"),
     *   @SWG\Property(property = "is_one_active", type = "integer", description = "Только один активный"),
     *   @SWG\Property(property = "currency_id", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)"),
     *   @SWG\Property(property = "serviceType", type = "object", description = "Тип услуги (ВАТС, телефония, интернет и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна клиента", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_country", type = "object", description = "Страна номера", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_status", type = "object", description = "Статус (публичный, специальный, архивный и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_person", type = "object", description = "Для кого действует тариф (для всех, физиков, юриков)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_tag", type = "object", description = "Тэг (хит продаж)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_tags", type = "object", description = "Тэги тарифа", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "tariff_resources", type = "array", description = "Ресурсы (дисковое пространство, абоненты, линии и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffResourceRecord")),
     *   @SWG\Property(property = "tariff_periods", type = "array", description = "Периоды (посуточно, помесячно и пр.) и их стоимость", @SWG\Items(ref = "#/definitions/tariffPeriodRecord")),
     *   @SWG\Property(property = "is_termination", type = "integer", description = "Телефония. Плата за входящие звонки?"),
     *   @SWG\Property(property = "tarification_free_seconds", type = "integer", description = "Телефония. Бесплатно, секунд"),
     *   @SWG\Property(property = "tarification_interval_seconds", type = "integer", description = "Телефония. 'Интервал билингования, секунд"),
     *   @SWG\Property(property = "tarification_type", type = "integer", description = "Телефония. Тип округления. 1 - round, 2 - ceil"),
     *   @SWG\Property(property = "tarification_min_paid_seconds", type = "integer", description = "Телефония. Минимальная плата, секунд"),
     *   @SWG\Property(property = "voip_group", type = "object", description = "Телефония. Группа (местные, междугородние, международные и пр.)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_cities", type = "array", description = "Телефония. Города", @SWG\Items(ref = "#/definitions/idNameRecord")),
     *   @SWG\Property(property = "voip_ndc_types", type = "array", description = "Телефония. Типы NDC", @SWG\Items(ref = "#/definitions/idNameRecord")),
     *   @SWG\Property(property = "organizations", type = "array", description = "Организации", @SWG\Items(ref = "#/definitions/idNameRecord")),
     *   @SWG\Property(property = "voip_package_minute", type = "array", description = "Телефония. Пакет. Предоплаченные минуты", @SWG\Items(ref = "#/definitions/voipPackageMinuteRecord")),
     *   @SWG\Property(property = "voip_package_price", type = "array", description = "Телефония. Пакет. Цена по направлениям", @SWG\Items(ref = "#/definitions/voipPackagePriceRecord")),
     *   @SWG\Property(property = "voip_package_pricelist", type = "array", description = "Телефония. Пакет. Прайслист", @SWG\Items(ref = "#/definitions/voipPackagePricelistRecord")),
     *   @SWG\Property(property = "default_packages", type = "array", description = "Дефолтные пакеты в тарифе", @SWG\Items(ref = "#/definitions/tariffRecord")),
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-tariffs", summary = "Список тарифов", operationId = "GetTariffs",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "is_default", type = "integer", description = "По умолчанию (0 / 1)", in = "query", default = ""),
     *   @SWG\Parameter(name = "is_postpaid", type = "integer", description = "0 - предоплата, 1 - постоплата", in = "query", default = ""),
     *   @SWG\Parameter(name = "is_one_active", type = "integer", description = "0 - активен, 1 - неактивен", in = "query", default = ""),
     *   @SWG\Parameter(name = "currency_id", type = "string", description = "Код валюты (RUB, USD, EUR и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "country_id", type = "integer", description = "ID страны телефонии. Поле правильнее переименовать в voip_country_id", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_country_id", type = "integer", description = "ID страны тарифа (витрины). Поле правильнее переименовать в country_id", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС (для определения по нему страны, валюты, тарифа и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_status_id", type = "integer", description = "ID статуса (публичный, специальный, архивный и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_person_id", type = "integer", description = "ID для кого действует тариф (для всех, физиков, юриков)", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_tag_id", type = "integer", description = "ID тега (хит продаж)", in = "query", default = ""),
     *   @SWG\Parameter(name = "tariff_tags_id", type = "string", description = "Теги тарифа", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_group_id", type = "integer", description = "ID группы телефонии (местные, междугородние, международные и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_city_id", type = "integer", description = "ID города телефонии", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_ndc_type_id", type = "integer", description = "ID типа NDC телефонии", in = "query", default = ""),
     *   @SWG\Parameter(name = "organization_id", type = "integer", description = "ID организации", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_number", type = "string", description = "Номер телефонии", in = "query", default = ""),
     *   @SWG\Parameter(name = "account_tariff_id", type = "integer", description = "ID услуги ЛС", in = "query", default = ""),
     *   @SWG\Parameter(name = "is_include_vat", type = "integer", description = "Включая НДС (0 / 1)", in = "query", default = ""),
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
     * @param int $service_type_id
     * @param int $country_id
     * @param int $tariff_country_id
     * @param null $voip_country_id
     * @param int $client_account_id
     * @param string $currency_id
     * @param int $is_default
     * @param int $is_postpaid
     * @param int $is_one_active
     * @param int $tariff_status_id
     * @param int $tariff_person_id
     * @param int $tariff_tag_id
     * @param int $tariff_tags_id
     * @param int $voip_group_id
     * @param int $voip_city_id
     * @param int $voip_ndc_type_id
     * @param int $organization_id
     * @param string $voip_number
     * @param int $account_tariff_id
     * @param bool $is_include_vat
     * @return array
     * @throws HttpException
     */
    public function actionGetTariffs(
        $id = null,
        $service_type_id = null,
        $country_id = null,
        $tariff_country_id = null,
        $voip_country_id = null,
        $client_account_id = null,
        $currency_id = null,
        $is_default = null,
        $is_postpaid = null,
        $is_one_active = null,
        $tariff_status_id = null,
        $tariff_person_id = null,
        $tariff_tag_id = null,
        $tariff_tags_id = null,
        $voip_group_id = null,
        $voip_city_id = null,
        $voip_ndc_type_id = null,
        $organization_id = null,
        $voip_number = null,
        $account_tariff_id = null,
        $is_include_vat = null
    )
    {
        $methodName = __FUNCTION__;
        \Yii::info(
            print_r([
                $methodName,
                $id,
                $service_type_id,
                $country_id,
                $tariff_country_id,
                $voip_country_id,
                $client_account_id,
                $currency_id,
                $is_default,
                $is_postpaid,
                $is_one_active,
                $tariff_status_id,
                $tariff_person_id,
                $tariff_tag_id,
                $tariff_tags_id,
                $voip_group_id,
                $voip_city_id,
                $voip_ndc_type_id,
                $organization_id,
                $voip_number,
                $account_tariff_id,
                $is_include_vat
            ], true),
            \app\modules\uu\Module::LOG_CATEGORY_API
        );

        $id = (int)$id;
        $service_type_id = (int)$service_type_id;

        // "сначала намечались торжества. Потом аресты. Потом решили совместить" (С) К/ф "Тот самый Мюнхгаузен"
//        $origCountryId = $country_id;
//        $voip_country_id = (int)$country_id; // страна телефонного номера. Выбирается явно. Путаница с именами для обратной совместимости c API.
//        $country_id = null; // страна клиента. Это зависит от точки входа (или организации) клиента, а не выбирается явно

        if (!$voip_country_id) {
            list($country_id, $voip_country_id) = [$tariff_country_id, $country_id];
        }

        $country_id = (int)$country_id;
        $voip_country_id = (int)$voip_country_id;

        $client_account_id = (int)$client_account_id;
        $tariff_status_id = (int)$tariff_status_id;
        $tariff_person_id = (int)$tariff_person_id;
        $tariff_tag_id = (int)$tariff_tag_id;
        if ($tariff_tags_id && !is_numeric($tariff_tags_id) && !is_array($tariff_tags_id)) {
            $tariff_tags_id = preg_split('/\D+/', $tariff_tags_id);
        }
        $voip_group_id = (int)$voip_group_id;
        $voip_city_id = (int)$voip_city_id;
        $voip_ndc_type_id = (int)$voip_ndc_type_id;
        $organization_id = (int)$organization_id;
        $account_tariff_id = (int)$account_tariff_id;

        if ($account_tariff_id) {
            /** @var AccountTariff $accountTariff */
            $accountTariff = AccountTariff::find()->where(['id' => $account_tariff_id])->one();
            if (!$accountTariff) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный account_tariff_id', AccountTariff::ERROR_CODE_USAGE_MAIN);
            }

            $client_account_id = $accountTariff->client_account_id;
            $voip_number = $accountTariff->prev_account_tariff_id ?
                $accountTariff->prevAccountTariff->voip_number :
                $accountTariff->voip_number;

            $voip_city_id = $accountTariff->city_id;
        }

        if ($client_account_id) {

            $clientAccount = ClientAccount::findOne(['id' => $client_account_id]);
            if (!$clientAccount) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный client_account_id', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
            }

            if (!$currency_id) {
                $currency_id = $clientAccount->currency;
            }

            $is_postpaid = $clientAccount->is_postpaid;
            $organization_id = $clientAccount->contract->organization_id;

            $tariff_person_id = ($clientAccount->contragent->legal_type == ClientContragent::PERSON_TYPE) ?
                TariffPerson::ID_NATURAL_PERSON :
                TariffPerson::ID_LEGAL_PERSON;

            // tariff_country_id
            $country_id = $clientAccount->getUuCountryId();
            $is_include_vat = $clientAccount->is_voip_with_tax;

            switch ($service_type_id) {

                case ServiceType::ID_VOIP:
                    if (!$voip_number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Не указан телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    /** @var \app\models\Number $number */
                    $number = Number::findOne(['number' => $voip_number]);
                    if (!$number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    if (!$account_tariff_id && $number->status != Number::STATUS_INSTOCK) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Телефонный номер уже занят', AccountTariff::ERROR_CODE_USAGE_NUMBER_NOT_IN_STOCK);
                    }

                    $tariff_status_id = $number->didGroup->getTariffStatusMain($clientAccount->price_level);
                    $voip_ndc_type_id = $number->ndc_type_id;
                    $voip_country_id = $number->country_code;
                    break;

                case ServiceType::ID_VOIP_PACKAGE_CALLS:
                case ServiceType::ID_VOIP_PACKAGE_SMS:
                case ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY:
                    if (!$voip_number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Не указан телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    /** @var \app\models\Number $number */
                    $number = Number::findOne(['number' => $voip_number]);
                    if (!$number) {
                        throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный телефонный номер', AccountTariff::ERROR_CODE_NUMBER_NOT_FOUND);
                    }

                    $tariff_status_id = $number->didGroup->getTariffStatusPackage($clientAccount->price_level);
                    $voip_ndc_type_id = $number->ndc_type_id;

                    //если Ndc тип мобильный и сервис тип смс либо интернет - обнуляем voip_ndc_type, т.к у этих сервис типов нету ndc типа.
                    if ($voip_ndc_type_id == NdcType::ID_MOBILE && ($service_type_id == ServiceType::ID_VOIP_PACKAGE_SMS || $service_type_id == ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY)) {
                        $voip_ndc_type_id = null;
                    }
                    
                    $voip_country_id = $number->country_code;
                    break;
            }
        }

//        if ($service_type_id == ServiceType::ID_VOIP_PACKAGE_CALLS) {
//            $voip_country_id = null;
//            $country_id = $origCountryId;
//        }

//        if (!$tariff_status_id) {
//            $tariff_status_id = TariffStatus::ID_PUBLIC;
//        }
//
//        if(
//            in_array($service_type_id, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS])
//            && ($voip_ndc_type_id == NdcType::ID_GEOGRAPHIC || !$voip_ndc_type_id)
//            && !$voip_country_id
//            && !$voip_city_id) {
//            $voip_country_id = Country::RUSSIA;
//        }
//
//        if (!$currency_id) {
//            $currency_id = Currency::RUB;
//        }

        // @todo надо ли только статус "публичный" для ватс?

         $params = [
             'id' => $id,
             'service_type_id' => $service_type_id,
             'country_id' => $country_id,
             'tariff_country_id' => $tariff_country_id,
             'currency_id' => $currency_id,
             'is_default' => $is_default,
             'is_postpaid' => $is_postpaid,
             'is_one_active' => $is_one_active,
             'tariff_status_id' => $tariff_status_id,
             'tariff_person_id' => $tariff_person_id,
             'tariff_tag_id' => $tariff_tag_id,
             'tariff_tags_id' => $tariff_tags_id,
             'voip_group_id' => $voip_group_id,
             'voip_city_id' => $voip_city_id,
             'voip_ndc_type_id' => $voip_ndc_type_id,
             'organization_id' => $organization_id,
             'is_include_vat' => $is_include_vat,
             'voip_country_id' =>$voip_country_id,
         ];

//        $tariffQuery = TariffFilter::getListQuery($id, $service_type_id, $country_id, $currency_id, $is_default, $is_postpaid, $is_one_active, $tariff_status_id, $tariff_person_id, $tariff_tag_id, $tariff_tags_id, $voip_group_id, $voip_city_id, $voip_ndc_type_id, $organization_id, $is_include_vat, $voip_country_id);
        $tariffQuery = TariffFilter::getListQuery($params);
        $result = [];
        $defaultPackageRecordsFetched = null;

        /** @var Tariff $tariff */
        foreach ($tariffQuery->all() as $tariff) {
            if ($tariff->service_type_id == ServiceType::ID_VOIP) {
                if (is_null($defaultPackageRecordsFetched)) {
                    $defaultPackageRecordsFetched = $this->actionGetTariffs(
                        $id_tmp = null,
                        ServiceType::ID_VOIP_PACKAGE_CALLS,
                        $country_id,
                        $tariff_country_id,
                        $voip_country_id,
                        $client_account_id,
                        $currency_id,
                        $is_default_tmp = 1,
                        $is_one_active_tmp = 1,
                        $is_postpaid_tmp = null,
                        $tariff_status_id,
                        $tariff_person_id,
                        $tariff_tag_id_tmp = null,
                        $tariff_tags_id_tmp = null,
                        $voip_group_id,
                        $voip_city_id,
                        $voip_ndc_type_id,
                        $organization_id_tmp = null, // пакеты телефонии - по стране, все остальное - по организации
                        $voip_number,
                        $account_tariff_id
                    );
                }

                $defaultPackageRecords = $defaultPackageRecordsFetched;
            } else {
                $defaultPackageRecords = [];
            }

            $tariffRecord = $this->_getTariffRecord($tariff, $tariff->tariffPeriods);
            $tariffRecord['default_packages'] = $defaultPackageRecords;
            $result[] = $tariffRecord;
        }

        return $result;
    }

    /**
     * @param Tariff $tariff
     * @param TariffPeriod|TariffPeriod[] $tariffPeriod
     * @param array $minutesStatistic
     * @return array
     */
    private function _getTariffRecord($tariff, $tariffPeriod, $minutesStatistic = [])
    {
        if (!$tariff || !$tariffPeriod) {
            return null;
        }

        $cacheKey = 'uuapitariff' . $tariff->id;

        if (!($data = \Yii::$app->cache->get($cacheKey))) {

            $package = $tariff->package;
            $tariffVoipCountries = $tariff->tariffVoipCountries;
            $tariffVoipCountry = reset($tariffVoipCountries);
            $tariffCountries = $tariff->tariffCountries;

            $data = [
                'id' => $tariff->id,
                'name' => $tariff->name,
                'count_of_validity_period' => $tariff->count_of_validity_period,
                'is_autoprolongation' => $tariff->is_autoprolongation,
                'is_charge_after_blocking' => $tariff->is_charge_after_blocking,
                'is_include_vat' => $tariff->is_include_vat,
                'is_default' => $tariff->is_default,
                'is_postpaid' => $tariff->is_postpaid,
                'is_one_active' => $tariff->is_one_active,
                'currency' => $tariff->currency_id,
                'service_type' => $this->_getIdNameRecord($tariff->serviceType),
                'country' => $this->_getIdNameRecord($tariffVoipCountry ? $tariffVoipCountry->country : null, 'code'), // @todo multi и переименовать в voip_countries
                'countries' => $this->_getIdNameRecord($tariffCountries, 'country_id'),
                'voip_countries' => $this->_getIdNameRecord($tariffVoipCountries, 'country_id'),
                'tariff_status' => $this->_getIdNameRecord($tariff->status),
                'tariff_person' => $this->_getIdNameRecord($tariff->person),
                'tariff_tag' => $this->_getIdNameRecord($tariff->tag),
                'tariff_tags' => $this->_getIdNameRecord($tariff->tariffTags, 'tag_id'),
                'tariff_resources' => $this->_getTariffResourceRecord($tariff->tariffResources),
                'tariff_periods' => null, //$this->_getTariffPeriodRecord($tariffPeriod),
                'is_termination' => $package ? $package->is_termination : null,
                'tarification_free_seconds' => $package ? $package->tarification_free_seconds : null,
                'tarification_interval_seconds' => $package ? $package->tarification_interval_seconds : null,
                'tarification_type' => $package ? $package->tarification_type : null,
                'tarification_min_paid_seconds' => $package ? $package->tarification_min_paid_seconds : null,
                'voip_group' => $this->_getIdNameRecord($tariff->voipGroup),
                'voip_cities' => $this->_getIdNameRecord($tariff->voipCities, 'city_id'),
                'voip_ndc_types' => $this->_getIdNameRecord($tariff->voipNdcTypes, 'ndc_type_id'),
                'organizations' => $this->_getIdNameRecord($tariff->organizations, 'organization_id'),
                'voip_package_pricelist' => $this->_getVoipPackagePricelistRecord($tariff->packagePricelists),
                'voip_package_price_internet' => $this->_getVoipPackagePriceV2Record($tariff->packagePricelistsNnpInternet, true),
                'voip_package_price_sms' => $this->_getVoipPackagePriceV2Record($tariff->packagePricelistsNnpSms, true),
            ];

            if (array_key_exists($tariff->service_type_id, ServiceType::$packages)) {
                $data['overview'] = $this->_getOverview($tariff->overview);
            }

            Yii::$app->cache->set($cacheKey, $data, DependecyHelper::DEFAULT_TIMELIFE, (new TagDependency(['tags' => [DependecyHelper::TAG_PRICELIST]])));
        }

        $data['tariff_periods'] = $this->_getTariffPeriodRecord($tariffPeriod);
        $data['voip_package_minute'] = $this->_getVoipPackageMinuteRecord($tariff->packageMinutes, $minutesStatistic);

        return $data;
    }

    private function _getOverview($overview)
    {
        $toRet = [];
        $lines = explode("\n", $overview);

        foreach ($lines as $line) {
            $line = trim(str_replace("\r", '', $line));

            if (!$line) {
                continue;
            }

            $pos = false;
            $beforePos = $line;
            $afterPos = '';
            $column = false;

            if ($pos = strpos($line, '=')) {
                $type = 'price';
                $column = 'price';
            } elseif ($pos = strpos($line, '#')) {
                $type = 'pricelist_link';
                $column = 'pricelist_id';
            } elseif ($pos = strpos($line, '|')) {
                $type = 'link';
                $column = 'link';
            } else {
                $type = 'text';
            }

            if ($pos) {
                $beforePos = trim(substr($line, 0, $pos));
                $afterPos = trim(substr($line, $pos+1, strlen($line)));
            }

            $json = [
                    'type' => $type,
                    'text' => $beforePos,
                ] + ($column ? [$column => $afterPos] : []);

            $toRet[] = $json;
        }
        return $toRet;
    }


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
        }

        if ($model) {
            $isCheckable = !$model->resource->isNumber();
            $value = [
                'id' => $model->id,
                'is_show_resource' => (bool)$model->is_show_resource,
                'is_checkable' => $isCheckable,
                'is_editable' => (bool)$model->is_can_manage,
                'is_checked' => $isCheckable ? (bool)$model->amount : null,
                'amount' => $isCheckable ? null : $model->amount,
                'price_per_unit' => $model->price_per_unit,
                'price_min' => $model->price_min,
                'resource' => $this->_getResourceRecord($model->resource),
            ];

            return $value;
        }

        return null;
    }

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
        }

        if ($model) {
            return [
                'id' => $model->id,
                'price_setup' => $model->price_setup,
                'price_per_period' => $model->price_per_period,
                'price_per_charge_period' => round($model->price_per_period * ($model->chargePeriod->monthscount ?: 1 / 30), 2),
                'price_min' => $model->price_min,
                'charge_period' => $this->_getIdNameRecord($model->chargePeriod),
            ];
        }

        return null;
    }

    /**
     * @param PackageMinute|PackageMinute[] $packageMinutes
     * @param array $minutesStatistic
     * @return array
     */
    private function _getVoipPackageMinuteRecord($packageMinutes, $minutesStatistic = [])
    {
        if (!$packageMinutes) {
            return null;
        }

        if (is_array($packageMinutes)) {
            $result = [];
            foreach ($packageMinutes as $packageMinute) {
                $result[] = $this->_getVoipPackageMinuteRecord($packageMinute, $minutesStatistic);
            }

            return $result;
        }

        $minuteStatistic = null;
        foreach ($minutesStatistic as $minuteStatisticTmp) {
            if ($minuteStatisticTmp['i_nnp_package_minute_id'] == $packageMinutes->id) {
                $minuteStatistic = $minuteStatisticTmp['i_used_seconds'];
                break;
            }
        }

        return [
            'destination' => (string)$packageMinutes->destination,
            'minute' => $packageMinutes->minute,
            'spent_seconds' => $minuteStatistic,
        ];
    }

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
     * @param PackagePricelistNnp[] $packagePriceLists
     * @param bool $isNames - только имена и id прайс-листов
     * @return array
     */
    private function _getVoipPackagePriceV2Record($packagePriceLists, $isNames = false)
    {
        if (!$packagePriceLists) {
            return null;
        }

        $result = [];
        foreach ($packagePriceLists as $packagePriceList) {
            if ($isNames) {
                $result[] = $this->_getIdNameRecord($packagePriceList->pricelistNnp);
            } else {
                $result = array_merge($result, Pricelist::getVoipPackagePriceV2Record($packagePriceList->nnp_pricelist_id));
            }
        }

        return $result;
    }

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
     * @SWG\Definition(definition = "TariffPeriodForExcelRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "price_per_period", type = "number", description = "Абонентская плата")
     * ),
     *
     * @SWG\Definition(definition = "AccountTariffForExcelRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID"),
     *   @SWG\Property(property = "date_from", type = "string", description = "Дата включения. Всегда указана. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "date_to", type = "string", description = "Дата выключения. Если не выключено и не планируется - null. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "city", type = "object", description = "Город", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона"),
     *   @SWG\Property(property = "beauty_level", type = "integer", description = "Уровень красивости номера телефонии (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)"),
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC номера телефонии"),
     *   @SWG\Property(property = "tariff", type = "array", description = "Для включенной услуги - текущий тариф. Для выключенной - последний действующий тариф", @SWG\Items(ref = "#/definitions/TariffPeriodForExcelRecord"))
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-account-tariffs-for-excel", summary = "Список услуг у ЛС для выгрузки в Excel", operationId = "GetAccountTariffsForExcel",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "query", default = "2"),
     *
     *   @SWG\Response(response = 200, description = "Список услуг у ЛС для выгрузки в Excel",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/AccountTariffForExcelRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $client_account_id
     * @param int $service_type_id
     * @return array
     * @throws HttpException
     */
    public function actionGetAccountTariffsForExcel(
        $client_account_id,
        $service_type_id
    )
    {
        $client_account_id = (int)$client_account_id;
        $service_type_id = (int)$service_type_id;

        !$service_type_id && $service_type_id = ServiceType::ID_VOIP;

        $accountTariffQuery = AccountTariffFilter::getListForExcelQuery($client_account_id, $service_type_id);

        $result = [];
        foreach ($accountTariffQuery->each() as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            $result[] = $this->_getAccountTariffForExcelRecord($accountTariff);
        }

        return $result;
    }

    /**
     * @param AccountTariff $accountTariff
     * @return array
     */
    private function _getAccountTariffForExcelRecord($accountTariff)
    {
        $number = $accountTariff->number;
        $accountTariffLogs = $accountTariff->accountTariffLogs;
        $accountTariffLogFirst = end($accountTariffLogs); // первый по времени идет последним в этом списке
        $accountTariffLogLast = array_shift($accountTariffLogs); // последний по времени (как в прошлом, так и в будущем) идет первым в этом списке
        $lastTariffPeriod = $accountTariffLogLast->tariff_period_id ? // последний непустой тариф / период
            $accountTariffLogLast->tariffPeriod :
            array_shift($accountTariffLogs)->tariffPeriod;
        return [
            'id' => $accountTariff->id,
            'date_from' => $accountTariffLogFirst->actual_from,
            'date_to' => $accountTariffLogLast->tariff_period_id ? null : $accountTariffLogLast->actual_from, // тариф / период есть - значит, закрытия нет
            'city' => $this->_getIdNameRecord($accountTariff->city),
            'voip_number' => $accountTariff->voip_number,
            'beauty_level' => $number ? $number->beauty_level : null,
            'ndc' => $number ? $number->ndc : null,
            'tariff' => $this->_getTariffPeriodForExcelRecord($lastTariffPeriod),
        ];
    }

    /**
     * @param TariffPeriod $tariffPeriod
     * @return array
     */
    private function _getTariffPeriodForExcelRecord($tariffPeriod)
    {
        $tariff = $tariffPeriod->tariff;
        return [
            'id' => $tariff->id,
            'name' => $tariff->name,
            'price_per_period' => $tariffPeriod->price_per_period,
        ];
    }

    /**
     * @SWG\Definition(definition = "accountTariffLogRecord", type = "object",
     *   @SWG\Property(property = "tariff", type = "object", description = "Тариф / период", @SWG\Items(ref = "#/definitions/tariffRecord")),
     *   @SWG\Property(property = "actual_from", type = "string", description = "Дата, с которой этот тариф действует. ГГГГ-ММ-ДД"),
     * ),
     *
     * @SWG\Definition(definition = "accountTariffResourceRecord", type = "object",
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс", @SWG\Items(ref = "#/definitions/resourceRecord")),
     *   @SWG\Property(property = "log", type = "array", description = "Лог смены количества ресурса. По убыванию даты", @SWG\Items(ref = "#/definitions/accountTariffResourceLogRecord")),
     * ),
     *
     * @SWG\Definition(definition = "accountTariffResourceLogRecord", type = "object",
     *   @SWG\Property(property = "amount", type = "float", description = "Значение"),
     *   @SWG\Property(property = "actual_from", type = "string", description = "Дата, с которой это значение действует. ГГГГ-ММ-ДД"),
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
     *   @SWG\Property(property = "beauty_level", type = "integer", description = "Уровень красивости номера телефонии (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)"),
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC номера телефонии"),
     *   @SWG\Property(property = "default_actual_from", type = "string", description = "Дата, с которой по умолчанию будет применяться смена тарифа или закрытие. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "account_tariff_logs", type = "array", description = "Лог тарифов", @SWG\Items(ref = "#/definitions/accountTariffLogRecord")),
     *   @SWG\Property(property = "calltracking_params", type = "string", description = "Параметры Сalltracking"),
     *   @SWG\Property(property = "resources", type = "array", description = "Ресурсы услуги", @SWG\Items(ref = "#/definitions/accountTariffResourceRecord")),
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-account-tariffs", summary = "Список услуг у ЛС", operationId = "GetAccountTariffs",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "query", default = ""),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона (кроме телефонии)", in = "query", default = ""),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города (только для телефонии)", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "query", default = ""),
     *   @SWG\Parameter(name = "prev_account_tariff_id", type = "integer", description = "ID основной услуги ЛС. Если список услуг пакета телефонии, то можно здесь указать ID услуги телефонии", in = "query", default = ""),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Сдвиг при пагинации", in = "query", default = "0"),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Не более 100 записей. Можно только уменьшить", in = "query", default = "50"),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Сдвиг при пагинации. Если не указано - 0", in = "query", default = "0"),
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
     * @param int $id
     * @param int $service_type_id
     * @param int $client_account_id
     * @param int $region_id
     * @param int $city_id
     * @param int $voip_number
     * @param int $prev_account_tariff_id
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionGetAccountTariffs(
        $id = null,
        $service_type_id = null,
        $client_account_id = null,
        $region_id = null,
        $city_id = null,
        $voip_number = null,
        $prev_account_tariff_id = null,
        $limit = self::DEFAULT_LIMIT,
        $offset = 0
    )
    {
        $id = (int)$id;
        $service_type_id = (int)$service_type_id;
        $client_account_id = (int)$client_account_id;
        $region_id = (int)$region_id;
        $city_id = (int)$city_id;
        $prev_account_tariff_id = (int)$prev_account_tariff_id;

        if (!$id && !$service_type_id && !$client_account_id) {
            throw new HttpException(ModelValidationException::STATUS_CODE, 'Необходимо указать фильтр id, service_type_id или client_account_id', AccountTariff::ERROR_CODE_SERVICE_TYPE);
        }

        $limit = min($limit ?: self::DEFAULT_LIMIT, self::MAX_LIMIT);
        $accountTariffQuery = AccountTariffFilter::getListQuery($id, $service_type_id, $client_account_id, $region_id, $city_id, $voip_number, $prev_account_tariff_id, $limit, $offset);

        $result = [];
        foreach ($accountTariffQuery->all() as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            $result[] = $this->_getAccountTariffRecord($accountTariff);
        }

        return $result;
    }

    /**
     * @param AccountTariff|AccountTariff[] $accountTariff
     * @return array
     * @throws \yii\db\Exception
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

        if ($accountTariff->service_type_id === ServiceType::ID_VOIP_PACKAGE_CALLS) {
            $minutesStatistic = $accountTariff->getMinuteStatistic();
        } else {
            $minutesStatistic = [];
        }

        $number = $accountTariff->number;
        return [
            'id' => $accountTariff->id,
            'client_account_id' => $accountTariff->client_account_id,
            'service_type' => $this->_getIdNameRecord($accountTariff->serviceType),
            'region' => $this->_getIdNameRecord($accountTariff->region),
            'city' => $this->_getIdNameRecord($accountTariff->city),
            'prev_account_tariff_id' => $accountTariff->prev_account_tariff_id,
            'next_account_tariffs' => $this->_getAccountTariffRecord($accountTariff->nextAccountTariffsEager),
            'comment' => $accountTariff->comment,
            'voip_number' => $accountTariff->voip_number,
            'beauty_level' => $number ? $number->beauty_level : null,
            'ndc' => $number ? $number->ndc : null,
            'default_actual_from' => $accountTariff->getDefaultActualFrom(),
            'account_tariff_logs' => $this->_getAccountTariffLogRecord($accountTariff->accountTariffLogs, $minutesStatistic),
            'resources' => $this->_getAccountTariffResourceRecord($accountTariff),
            'calltracking_params' => $accountTariff->calltracking_params,
        ];
    }

    /**
     * @param AccountTariffLog|AccountTariffLog[] $model
     * @param array $minutesStatistic
     * @return array|null
     */
    private function _getAccountTariffLogRecord($model, $minutesStatistic = [])
    {
        if (is_array($model)) {
            $result = [];
            foreach ($model as $subModel) {
                $result[] = $this->_getAccountTariffLogRecord($subModel, $minutesStatistic);
            }

            return $result;
        }

        if ($model) {
            return [
                'tariff' => $model->tariffPeriod ?
                    $this->_getTariffRecord($model->tariffPeriod->tariff, $model->tariffPeriod, $minutesStatistic) :
                    null,
                'actual_from' => $model->actual_from,
            ];
        }

        return null;
    }

    /**
     * @param AccountTariff $accountTariff
     * @return array
     */
    private function _getAccountTariffResourceRecord($accountTariff)
    {
        $accountTariffResourceRecords = [];

        foreach ($accountTariff->serviceType->resources as $resource) {
            $accountTariffResourceLogs = $accountTariff->getAccountTariffResourceLogsByResourceId($resource->id);

            $accountTariffResourceRecords[] = [
                'resource' => $this->_getResourceRecord($resource),
                'log' => $this->_getAccountTariffResourceLogRecord($accountTariffResourceLogs),
            ];
        }

        return $accountTariffResourceRecords;
    }

    /**
     * @param AccountTariffResourceLog[] $accountTariffResourceLogs
     * @return array
     */
    private function _getAccountTariffResourceLogRecord($accountTariffResourceLogs)
    {
        $accountTariffResourceLogRecord = [];

        foreach ($accountTariffResourceLogs as $accountTariffResourceLog) {
            $accountTariffResourceLogRecord[] = [
                'amount' => $accountTariffResourceLog->amount,
                'actual_from' => $accountTariffResourceLog->actual_from,
            ];
        }

        return $accountTariffResourceLogRecord;
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
        }

        if ($model) {
            return [
                'date' => $model->date,
                'price' => $model->price,
                'tariff_id' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariff_period_id' => $model->tariff_period_id,
            ];
        }

        return null;
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
        }

        if ($model) {
            return [
                'date_from' => $model->date_from,
                'date_to' => $model->date_to,
                'price_per_period' => $model->period_price,
                'coefficient' => $model->coefficient,
                'price' => $model->price,
                'tariff_id' => $model->tariff_period_id ? $model->tariffPeriod->tariff_id : null,
                'tariff_period_id' => $model->tariff_period_id,
            ];
        }

        return null;
    }

    /**
     * @SWG\Definition(definition = "accountTariffLogLightRecord", type = "object",
     *   @SWG\Property(property = "tariff", type = "object", description = "Тариф / период", @SWG\Items(ref = "#/definitions/tariffRecord")),
     *   @SWG\Property(property = "activate_initial_date", type = "string", description = "Дата включения услуги. Указана всегда. Может быть как в прошлом, так и в будущем. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "activate_past_date", type = "string", description = "Дата, с которой этот тариф был включен и сейчас действует. Всегда в прошлом. Если null - еще не включен (тогда см. activate_future_date) или уже выключен (deactivate_past_date). ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "activate_future_date", type = "string", description = "Дата, с которой этот тариф будет включен, и его можно отменить. Всегда в будущем. Если null - в будущем изменений не будет. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "deactivate_past_date", type = "string", description = "Дата, с которой этот тариф был выключен, и сейчас не действует. Всегда в прошлом. Если null - не был выключен. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "deactivate_future_date", type = "string", description = "Дата, с которой этот тариф будет выключен, и его можно отменить. Всегда в будущем. Если null - в будущем изменений не будет. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "is_cancelable", type = "boolean", description = "Можно ли отменить смену тарифа или закрытие? Если в будущем назначена смена тарифа или закрытие"),
     *   @SWG\Property(property = "is_editable", type = "boolean", description = "Можно ли сменить тариф или отключить услугу? Если null - знаит, неприменимо"),
     * ),
     *
     * @SWG\Definition(definition = "accountTariffResourceLogLightRecord", type = "object",
     *   @SWG\Property(property = "amount", type = "float", description = "Значение"),
     *   @SWG\Property(property = "activate_past_date", type = "string", description = "Дата, с которой это значение ресурса было изменено и сейчас действует. Всегда в прошлом. Если null - еще не включено (тогда см. activate_future_date). ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "activate_future_date", type = "string", description = "Дата, с которой это значение ресурса будет изменено, и его можно отменить. Всегда в будущем. Если null - в будущем изменений не будет. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "is_cancelable", type = "boolean", description = "Можно ли отменить смену этого значения ресурса? Если в будущем назначена смена этого значения ресурса"),
     *   @SWG\Property(property = "is_editable", type = "boolean", description = "Можно ли сменить это значение ресурса?"),
     * ),
     *
     * @SWG\Definition(definition = "accountTariffResourceLightRecord", type = "object",
     *   @SWG\Property(property = "resource", type = "object", description = "Ресурс", @SWG\Items(ref = "#/definitions/resourceRecord")),
     *   @SWG\Property(property = "free_amount", type = "number", description = "Условно-бесплатно включено в текущий тариф, ед. Если null - изменить нельзя"),
     *   @SWG\Property(property = "price_per_unit", type = "number", description = "Цена за превышение в текущем тарифе, ¤/ед. Если null - изменить нельзя"),
     *   @SWG\Property(property = "price_min", type = "number", description = "Мин. стоимость за месяц в текущем тарифе, ¤. Если null - изменить нельзя"),
     *   @SWG\Property(property = "log", type = "array", description = "Сокращенный лог ресурсов (только текущий и будущий). По убыванию даты", @SWG\Items(ref = "#/definitions/accountTariffResourceLogLightRecord")),
     * ),
     *
     * @SWG\Definition(definition = "accountTariffWithPackagesRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "ID услуги"),
     *   @SWG\Property(property = "service_type", type = "object", description = "Тип услуги", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "region", type = "object", description = "Регион", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "voip_number", type = "integer", description = "Если 4-5 символов - номер линии, если больше - номер телефона"),
     *   @SWG\Property(property = "voip_city", type = "object", description = "Город", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "beauty_level", type = "integer", description = "Уровень красивости номера телефонии (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)"),
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC номера телефонии"),
     *   @SWG\Property(property = "ndc_type_id", type = "integer", description = "Тип NDC номера телефонии"),
     *   @SWG\Property(property = "is_active", type = "boolean", description = "Действует ли?"),
     *   @SWG\Property(property = "is_package_addable", type = "boolean", description = "Можно ли подключить пакет?"),
     *   @SWG\Property(property = "is_cancelable", type = "boolean", description = "Можно ли отменить смену тарифа или закрытие? Если в будущем назначена смена тарифа или закрытие"),
     *   @SWG\Property(property = "is_editable", type = "boolean", description = "Можно ли сменить тариф или отключить услугу?"),
     *   @SWG\Property(property = "is_fmc_editable", type = "boolean", description = "Можно ли редактировать ресурс FMC? Если null - неприменимо"),
     *   @SWG\Property(property = "is_fmc_active", type = "boolean", description = "Включен ли ресурс FMC?"),
     *   @SWG\Property(property = "is_mobile_outbound_editable", type = "boolean", description = "Можно ли редактировать ресурс Исх.моб.связь? Если null - неприменимо"),
     *   @SWG\Property(property = "is_mobile_outbound_active", type = "boolean", description = "Включен ли ресурс Исх.моб.связь?"),
     *   @SWG\Property(property = "log", type = "array", description = "Сокращенный лог тарифов (только текущий и будущий). По убыванию даты", @SWG\Items(ref = "#/definitions/accountTariffLogLightRecord")),
     *   @SWG\Property(property = "resources", type = "array", description = "Ресурсы", @SWG\Items(ref = "#/definitions/accountTariffResourceLightRecord")),
     *   @SWG\Property(property = "default_actual_from", type = "string", description = "Дата, с которой по умолчанию будет применяться смена тарифа или закрытие. И с которой уменьшение количества ресурса повлияет на баланс. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "packages", type = "array", description = "Услуги пакета телефонии (если это телефония)", @SWG\Items(type = "array", @SWG\Items(ref = "#/definitions/accountTariffWithPackagesRecord"))),
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-account-tariffs-with-packages", summary = "Список услуг у ЛС с пакетами", operationId = "GetAccountTariffsWithPackages",
     *   @SWG\Parameter(name = "id", type = "integer", description = "ID услуги", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "Тип услуги", in = "query", default = ""),
     *   @SWG\Parameter(name = "voip_number", type = "string", description = "Номер телефонии", in = "query", default = ""),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Не более 100 записей. Можно только уменьшить", in = "query", default = "50"),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Сдвиг при пагинации. Если не указано - 0", in = "query", default = "0"),
     *
     *   @SWG\Response(response = 200, description = "Список услуг у ЛС с пакетами",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/accountTariffWithPackagesRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $id
     * @param int $client_account_id
     * @param int $service_type_id
     * @param string $voip_number
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws HttpException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionGetAccountTariffsWithPackages(
        $id = null,
        $client_account_id = null,
        $service_type_id = null,
        $voip_number = null,
        $limit = self::DEFAULT_LIMIT,
        $offset = 0
    )
    {
        $methodName = __FUNCTION__;
        \Yii::info(
            print_r([
                $methodName,
                $id,
                $client_account_id,
                $service_type_id,
                $voip_number,
                $limit,
                $offset
            ], true),
            \app\modules\uu\Module::LOG_CATEGORY_API
        );

        if (!$id && !$client_account_id && !$voip_number) {
            throw new HttpException(ModelValidationException::STATUS_CODE, 'Необходимо указать фильтр id или client_account_id или voip_number', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
        }

        $limit = min($limit ?: self::DEFAULT_LIMIT, self::MAX_LIMIT);
        $accountTariffQuery = AccountTariffFilter::getListWithPackagesQuery($id, $client_account_id, $service_type_id, $voip_number, $limit, $offset);

        $result = [];
        foreach ($accountTariffQuery->all() as $accountTariff) {
            $result[] = $this->_getAccountTariffWithPackagesRecord($accountTariff);
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "accountTariffsCount", type = "object",
     *   @SWG\Property(property = "count", type = "integer", description = "Количество услуг"),
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-account-tariffs-count", summary = "Количество всех услуг у ЛС", operationId = "GetAccountTariffsCount",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", required = true, default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "Тип услуги", in = "query", required = true, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Количество всех услуг у ЛС",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/accountTariffsCount"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $client_account_id
     * @param int $service_type_id
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAccountTariffsCount($client_account_id, $service_type_id)
    {
        $model = DynamicModel::validateData([
            'client_account_id' => $client_account_id,
            'service_type_id' => $service_type_id
        ], [
            [['client_account_id', 'service_type_id'], 'required'],
            [['client_account_id', 'service_type_id'], 'integer']
        ]);
        $model->validateWithException();

        $accountTariffQuery = AccountTariff::find()
            ->where([
                'client_account_id' => $client_account_id,
                'service_type_id' => $service_type_id
            ]);

        return [
            'count' => $accountTariffQuery->count(),
        ];
    }

    /**
     * Услуги
     *
     * @param AccountTariff $accountTariff
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    private function _getAccountTariffWithPackagesRecord($accountTariff)
    {
        $minutesStatistic = [];
        if ($accountTariff->service_type_id === ServiceType::ID_VOIP_PACKAGE_CALLS) {
            $minutesStatistic = $accountTariff->getMinuteStatistic();
        }

        $number = $accountTariff->number;

        if ($number) {
            $isFmcEditable = $number->isMobileOutboundEditable();
            $isFmcActive = $number->isFmcAlwaysActive() || (!$number->isFmcAlwaysInactive() && $accountTariff->getResourceValue(ResourceModel::ID_VOIP_FMC));

            $isMobileOutboundEditable = $number->isMobileOutboundEditable();
            $isMobileOutboundActive = $number->isMobileOutboundAlwaysActive() || (!$number->isMobileOutboundAlwaysInactive() && $accountTariff->getResourceValue(ResourceModel::ID_VOIP_MOBILE_OUTBOUND));
        } else {
            $isFmcEditable = $isFmcActive = null;
            $isMobileOutboundEditable = $isMobileOutboundActive = null;
        }

        /** @var AccountTariffLog $firstTariff */
        $lastLog = end($accountTariff->accountTariffLogs);
        $isDefaultTariff = true;

        if ($lastLog && $lastLog->tariff_period_id) {
            $isDefaultTariff = $lastLog->tariffPeriod->tariff->is_default;
        }

        $record = [
            'id' => $accountTariff->id,
            'service_type' => $this->_getIdNameRecord($accountTariff->serviceType),
            'region' => $this->_getIdNameRecord($accountTariff->region),
            'voip_number' => $accountTariff->voip_number,
            'voip_city' => $this->_getIdNameRecord($accountTariff->city),
            'beauty_level' => $number ? $number->beauty_level : null,
            'ndc' => $number ? $number->ndc : null,
            'ndc_type_id' => $number ? $number->ndc_type_id : null,
            'is_active' => $accountTariff->isActive(), // Действует ли?
            'is_package_addable' => $accountTariff->isPackageAddable(), // Можно ли подключить пакет?
            'is_cancelable' => $accountTariff->isLogCancelable(), // Можно ли отменить смену тарифа?
            'is_editable' => $accountTariff->isLogEditable(), // Можно ли сменить тариф или отключить услугу?
            'is_fmc_editable' => $isFmcEditable,
            'is_fmc_active' => $isFmcActive,
            'is_mobile_outbound_editable' => $isMobileOutboundEditable,
            'is_mobile_outbound_active' => $isMobileOutboundActive,
            'log' => $this->_getAccountTariffLogLightRecord($accountTariff->accountTariffLogs, $minutesStatistic),
            'resources' => $this->_getAccountTariffResourceLightRecord($accountTariff),
            'default_actual_from' => $accountTariff->getDefaultActualFrom(),
            'packages' => [],
            'account_tariff_light_ids' => !$isDefaultTariff ? $this->_getAccountTariffLights($accountTariff->id) : [],
        ];


        $packages = $accountTariff->nextAccountTariffsEager;
        if ($packages) {
            $record['packages'] = [];
            foreach ($packages as $package) {
                $record['packages'][] = $this->_getAccountTariffWithPackagesRecord($package);
            }
        }

        return $record;
    }

    public function _getAccountTariffLights($accountTariffId)
    {
        return AccountLogPeriod::find()
            ->where(['account_tariff_id' => $accountTariffId])
            ->select(['id', 'date_from', 'date_to'])
            ->orderBy(['date_from' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * @param AccountTariff $accountTariff
     * @return array
     */
    private function _getAccountTariffResourceLightRecord($accountTariff)
    {
        $accountTariffResourceRecords = [];

        $tariffPeriod = $accountTariff->tariffPeriod;
        $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;
        $tariffResourcesIndexedByResourceId = $tariff ? $tariff->tariffResourcesIndexedByResourceId : [];

        foreach ($accountTariff->serviceType->resources as $resource) {
            $tariffResource = isset($tariffResourcesIndexedByResourceId[$resource->id]) ? $tariffResourcesIndexedByResourceId[$resource->id] : null;

            if (!$tariffResource->is_show_resource) {
                continue;
            }

            $accountTariffResourceLogs = [];
            foreach ($accountTariff->accountTariffResourceLogsAll as $accountTariffResourceLog) {
                if ($accountTariffResourceLog->resource_id == $resource->id) {
                    $accountTariffResourceLogs[] = $accountTariffResourceLog;
                }
            }

            $accountTariffResourceRecords[] = [
                'resource' => $this->_getResourceRecord($resource),
                'free_amount' => $tariffResource ? $tariffResource->amount : null,
                'price_per_unit' => $tariffResource ? $tariffResource->price_per_unit : null,
                'price_min' => $tariffResource ? $tariffResource->price_min : null,
                'log' => $this->_getAccountTariffResourceLogLightRecord($accountTariffResourceLogs, $tariffResourcesIndexedByResourceId[$resource->id]->is_can_manage),
            ];
        }

        return $accountTariffResourceRecords;
    }

    /**
     * @param AccountTariffResourceLog[] $models
     * @param bool $isCanManage
     * @return array
     */
    private function _getAccountTariffResourceLogLightRecord($models, $isCanManage = false)
    {
        $result = [];

        $modelLast = array_shift($models);
        if (!$modelLast) {
            return $result;
        }

        $modelPrev = array_shift($models);

        $isCancelable = $modelLast->actual_from > date(DateTimeZoneHelper::DATE_FORMAT);
        if ($isCancelable) {

            // смена количества ресурса в будущем
            if ($modelPrev) {
                // текущее значение количества ресурса
                $result[] = [
                    'amount' => $modelPrev->amount,
                    'activate_past_date' => $modelPrev->actual_from,
                    'activate_future_date' => null,
                    'is_cancelable' => false,
                    'is_editable' => false,
                ];
            }

            // будущее значение количества ресурса
            $result[] = [
                'amount' => $modelLast->amount,
                'activate_past_date' => null,
                'activate_future_date' => $modelLast->actual_from,
                'is_cancelable' => (bool)$modelPrev, // если есть на что отменять
                'is_editable' => false,
            ];

        } else {

            // смена количества ресурса в прошлом
            $result[] = [
                'amount' => $modelLast->amount,
                'activate_past_date' => $modelLast->actual_from,
                'activate_future_date' => null,
                'is_cancelable' => false,
                'is_editable' => (bool)$isCanManage,
            ];

        }

        return $result;
    }

    /**
     * @param AccountTariffLog[] $models
     * @param array $minutesStatistic
     * @return array
     */
    private function _getAccountTariffLogLightRecord($models, $minutesStatistic = [])
    {
        $result = [];

        $modelLast = array_shift($models);
        if (!$modelLast) {
            return $result;
        }

        $modelPrev = array_shift($models);
        $modelFirst = array_pop($models);
        !$modelFirst && $modelFirst = $modelPrev;
        !$modelFirst && $modelFirst = $modelLast;

        $isCancelable = $modelLast->actual_from > date(DateTimeZoneHelper::DATE_FORMAT);

        if ($modelLast->tariff_period_id) {

            // действующий
            if ($isCancelable) {

                // смена тарифа в будущем
                if ($modelPrev) {
                    // текущий тариф
                    $result[] = [
                        'tariff' => $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod, $minutesStatistic),
                        'activate_initial_date' => $modelFirst->actual_from,
                        'activate_past_date' => $modelPrev->actual_from,
                        'activate_future_date' => null,
                        'deactivate_past_date' => null,
                        'deactivate_future_date' => null,
                        'is_cancelable' => false, // Можно ли отменить смену тарифа?
                        'is_editable' => false, // Можно ли сменить тариф или отключить услугу?
                    ];
                }

                // будущий
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelLast->tariffPeriod->tariff, $modelLast->tariffPeriod),
                    'activate_initial_date' => $modelFirst->actual_from,
                    'activate_past_date' => null,
                    'activate_future_date' => $modelLast->actual_from,
                    'deactivate_past_date' => null,
                    'deactivate_future_date' => null,
                    'is_cancelable' => true, // Можно ли отменить смену тарифа?
                    'is_editable' => false, // Можно ли сменить тариф или отключить услугу?
                ];

            } else {

                // смена тарифа в прошлом
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelLast->tariffPeriod->tariff, $modelLast->tariffPeriod, $minutesStatistic),
                    'activate_initial_date' => $modelFirst->actual_from,
                    'activate_past_date' => $modelLast->actual_from,
                    'activate_future_date' => null,
                    'deactivate_past_date' => null,
                    'deactivate_future_date' => null,
                    'is_cancelable' => false, // Можно ли отменить смену тарифа?
                    'is_editable' => true, // Можно ли сменить тариф или отключить услугу?
                ];

            }
        } else {

            // закрытый
            if ($isCancelable) {

                // закрытие тарифа в будущем
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod),
                    'activate_initial_date' => $modelFirst->actual_from,
                    'activate_past_date' => $modelPrev->actual_from,
                    'activate_future_date' => null,
                    'deactivate_past_date' => null,
                    'deactivate_future_date' => $modelLast->actual_from,
                    'is_cancelable' => true, // Можно ли отменить смену тарифа?
                    'is_editable' => false, // Можно ли сменить тариф или отключить услугу?
                ];

            } else {

                // закрытие тарифа в прошлом
                $result[] = [
                    'tariff' => $this->_getTariffRecord($modelPrev->tariffPeriod->tariff, $modelPrev->tariffPeriod),
                    'activate_initial_date' => $modelFirst->actual_from,
                    'activate_past_date' => null,
                    'activate_future_date' => null,
                    'deactivate_past_date' => $modelLast->actual_from,
                    'deactivate_future_date' => null,
                    'is_cancelable' => false, // Можно ли отменить смену тарифа?
                    'is_editable' => false, // Можно ли сменить тариф или отключить услугу?
                ];

            }
        }

        return $result;
    }

    /**
     * @SWG\Put(tags = {"UniversalTariffs"}, path = "/internal/uu/add-account-tariff", summary = "Добавить услугу ЛС", operationId = "AddAccountTariff",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "tariff_period_id", type = "integer", description = "ID периода тарифа (например, 100 руб/мес, 1000 руб/год)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой этот тариф будет действовать. ГГГГ-ММ-ДД. Если не указан, то с сегодня", in = "formData", default = ""),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона (кроме телефонии)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города (только для телефонии)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "formData", default = ""),
     *   @SWG\Parameter(name = "comment", type = "string", description = "Комментарий", in = "formData", default = ""),
     *   @SWG\Parameter(name = "prev_account_tariff_id", type = "integer", description = "ID основной услуги ЛС. Если добавляется услуга пакета телефонии, то необходимо здесь указать ID услуги телефонии", in = "formData", default = ""),
     *   @SWG\Parameter(name = "user_info", type = "string", description = "Информация о юзере (логин, IP, user-agent)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_async", type = "integer", description = "Асинхронная схема", in = "formData", default = "0"),
     *   @SWG\Parameter(name = "webhook_url", type = "string", description = "WebHook URL возврат результата при асинхронной схеме", in = "formData", default = ""),
     *   @SWG\Parameter(name = "request_id", type = "string", description = "идентификатор запроса для асинхронного ответа", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_create_user", type = "integer", description = "Создавать ли пользователя ЛК (при отсутствии: 1)", in = "formData", default = "1"),
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
     * @return int
     * @throws \yii\db\Exception
     * @throws Exception
     */

    public function actionAddAccountTariff()
    {
        $post = Yii::$app->request->post();

        return $this->_addAccountTariff($post);
    }

    /**
     * @SWG\Put(tags = {"UniversalTariffs"}, path = "/internal/uu/add-account-tariff__for-lk-mcn-ru", summary = "Добавить услугу ЛС (для lk.mcn.ru)", operationId = "AddAccountTariff_forLkMcnRu",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "service_type_id", type = "integer", description = "ID типа услуги (ВАТС, телефония, интернет и пр.)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "tariff_period_id", type = "integer", description = "ID периода тарифа (например, 100 руб/мес, 1000 руб/год)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой этот тариф будет действовать. ГГГГ-ММ-ДД. Если не указан, то с сегодня", in = "formData", default = ""),
     *   @SWG\Parameter(name = "region_id", type = "integer", description = "ID региона (кроме телефонии)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "city_id", type = "integer", description = "ID города (только для телефонии)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "voip_number", type = "integer", description = "Для телефонии: номер линии (если 4-5 символов) или телефона", in = "formData", default = ""),
     *   @SWG\Parameter(name = "comment", type = "string", description = "Комментарий", in = "formData", default = ""),
     *   @SWG\Parameter(name = "prev_account_tariff_id", type = "integer", description = "ID основной услуги ЛС. Если добавляется услуга пакета телефонии, то необходимо здесь указать ID услуги телефонии", in = "formData", default = ""),
     *   @SWG\Parameter(name = "user_info", type = "string", description = "Информация о юзере (логин, IP, user-agent)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_async", type = "integer", description = "Асинхронная схема", in = "formData", default = "0"),
     *   @SWG\Parameter(name = "webhook_url", type = "string", description = "WebHook URL возврат результата при асинхронной схеме", in = "formData", default = ""),
     *   @SWG\Parameter(name = "request_id", type = "string", description = "идентификатор запроса для асинхронного ответа", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_create_user", type = "integer", description = "Создавать ли пользователя ЛК (при отсутствии: 1)", in = "formData", default = "1"),
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
     * @return int
     * @throws \yii\db\Exception
     * @throws Exception
     */

    public function actionAddAccountTariff__forLkMcnRu()
    {
        $post = Yii::$app->request->post();

        $post[Trouble::OPTION_IS_FROM_LK_MCN_RU] = true;

        if (!\Yii::$app->user->getId()) {
            \Yii::$app->user->setIdentity(User::findOne(['id' => User::LK_USER_ID]));
        }

        return $this->_addAccountTariff($post);
    }

    public function _addAccountTariff($post)
    {
        if (isset($post['is_async']) && $post['is_async']) {
            $event = EventQueue::go(asyncModule::EVENT_ASYNC_ADD_ACCOUNT_TARIFF, $post);
            $requestId = isset($post['request_id']) && $post['request_id'] ? $post['request_id'] : $event->id;

            return ['request_id' => $requestId];
        }

        !isset($post['is_create_user']) && $post['is_create_user'] = true;
        $post['is_create_user'] = (int)(bool)$post['is_create_user'];


        $sem = Semaphore::me();
        $sem->acquire(Semaphore::ID_UU_CALCULATOR);

        $transaction = Yii::$app->db->beginTransaction();
        $accountTariff = new AccountTariff();
        $accountTariffLog = new AccountTariffLog;

        try {

            $accountTariff->setAttributes($post);
            $accountTariff->addParam('is_create_user', $post['is_create_user']);
            if (!$accountTariff->save()) {
                throw new ModelValidationException($accountTariff, $accountTariff->errorCode);
            }

            // записать в лог тарифа
            $accountTariffLog->account_tariff_id = $accountTariff->id;
            $accountTariffLog->setAttributes($post);
            if (!$accountTariffLog->actual_from_utc) {
                $accountTariffLog->actual_from_utc = (new \DateTime('00:00:00', $accountTariff->clientAccount->getTimezone()))
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);
            }

            if (!$accountTariffLog->save()) {
                throw new ModelValidationException($accountTariffLog, $accountTariffLog->errorCode);
            }

            $this->_checkTariff($accountTariff, $accountTariffLog);

            $roistatVisit = TroubleRoistatStore::getRoistatIdByAccountId($accountTariff->client_account_id);
            if (!$roistatVisit && isset($post[Trouble::OPTION_IS_FROM_LK_MCN_RU])) {
                $roistatVisit = TroubleRoistat::getChannelNameById(TroubleRoistat::CHANNEL_LK);
            }

            Trouble::dao()->notificateCreateAccountTariff(
                $accountTariff,
                $accountTariffLog,
                [],
                $roistatVisit ? ['roistat_visit' => $roistatVisit] : [],
            );
            $transaction->commit();
            $sem->release(Semaphore::ID_UU_CALCULATOR);
            return $accountTariff->id;
        } catch (Exception $e) {
            $transaction->rollBack();
            $sem->release(Semaphore::ID_UU_CALCULATOR);
            $code = $e->getCode();
            if ($code >= AccountTariff::ERROR_CODE_DATE_PREV && $code < AccountTariff::ERROR_CODE_USAGE_EMPTY) {
                \Yii::error(
                    print_r(['AddAccountTariff', $e->getMessage(), $post], true),
                    Module::LOG_CATEGORY_API
                );
            }

            $post['error'] = $e->getMessage();
            $post['file'] = $e->getFile() . ':' . $e->getLine();
            Trouble::dao()->notificateCreateAccountTariff($accountTariff, $accountTariffLog, $post);

            throw $e;
        }
    }

    /**
     * @SWG\Post(tags = {"UniversalTariffs"}, path = "/internal/uu/edit-account-tariff", summary = "Сменить тариф услуге ЛС", operationId = "EditAccountTariff",
     *   @SWG\Parameter(name = "account_tariff_ids[0]", type = "integer", description = "IDs услуг", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "account_tariff_ids[1]", type = "integer", description = "IDs услуг", in = "formData", default = ""),
     *   @SWG\Parameter(name = "tariff_period_id", type = "integer", description = "ID нового периода тарифа (например, 100 руб/мес, 1000 руб/год)", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой новый тариф будет действовать. ГГГГ-ММ-ДД. Если не указано - с начала следующего периода (точную дату см. в get-account-tariff/default_actual_from)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "user_info", type = "string", description = "Информация о юзере (логин, IP, user-agent)", in = "formData", default = ""),
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
     * @return int
     * @throws Exception
     */
    public function actionEditAccountTariff()
    {
        $postData = Yii::$app->request->post();

        return $this->editAccountTariff(
            isset($postData['account_tariff_ids']) ? $postData['account_tariff_ids'] : [],
            isset($postData['tariff_period_id']) ? $postData['tariff_period_id'] : null,
            (isset($postData['actual_from']) && $postData['actual_from']) ? $postData['actual_from'] : null,
            isset($postData['user_info']) ? $postData['user_info'] : ''
        );
    }

    /**
     * @param int[] $account_tariff_ids
     * @param int $tariff_period_id
     * @param string $actual_from
     * @param string $user_info
     * @return int
     * @throws HttpException
     * @throws Exception
     * @throws ModelValidationException
     */
    public function editAccountTariff($account_tariff_ids, $tariff_period_id, $actual_from, $user_info = '')
    {
        if (!$account_tariff_ids || !is_array($account_tariff_ids)) {
            throw new HttpException(ModelValidationException::STATUS_CODE, 'Не указан обязательный параметр account_tariff_ids', AccountTariff::ERROR_CODE_USAGE_EMPTY);
        }

        $transaction = Yii::$app->db->beginTransaction();
        $sem = Semaphore::me();
        $sem->acquire(Semaphore::ID_UU_CALCULATOR);
        try {

            foreach ($account_tariff_ids as $account_tariff_id) {

                $accountTariff = AccountTariff::findOne(['id' => (int)$account_tariff_id]);
                if (!$accountTariff) {
                    throw new HttpException(ModelValidationException::STATUS_CODE, 'Услуга с таким идентификатором не найдена ' . $account_tariff_id, AccountTariff::ERROR_CODE_USAGE_EMPTY);
                }

                // записать в лог тарифа
                $accountTariffLog = new AccountTariffLog;
                $accountTariffLog->account_tariff_id = $accountTariff->id;
                $accountTariffLog->tariff_period_id = $tariff_period_id;
                $accountTariffLog->actual_from = $actual_from ?: $accountTariff->getDefaultActualFrom();
                $accountTariffLog->user_info = $user_info;
                if (!$accountTariffLog->save()) {
                    throw new ModelValidationException($accountTariffLog, $accountTariffLog->errorCode);
                }

                $this->_checkTariff($accountTariff, $accountTariffLog);
            }

            Trouble::dao()->notificateCreateAccountTariff($accountTariff, $accountTariffLog);

            $transaction->commit();
            $sem->release(Semaphore::ID_UU_CALCULATOR);

            return true;

        } catch (Exception $e) {
            $transaction->rollBack();
            $sem->release(Semaphore::ID_UU_CALCULATOR);

            $code = $e->getCode();
            if ($code >= AccountTariff::ERROR_CODE_DATE_PREV && $code < AccountTariff::ERROR_CODE_USAGE_EMPTY) {
                \Yii::error(
                    print_r(['editAccountTariff', $e->getMessage(), $account_tariff_ids, $tariff_period_id, $actual_from, $user_info], true),
                    Module::LOG_CATEGORY_API
                );
            }
            throw $e;
        }
    }

    /**
     * @SWG\Post(tags = {"UniversalTariffs"}, path = "/internal/uu/close-account-tariff", summary = "Закрыть услугу ЛС", operationId = "CloseAccountTariff",
     *   @SWG\Parameter(name = "account_tariff_ids[0]", type = "integer", description = "IDs услуг", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "account_tariff_ids[1]", type = "integer", description = "IDs услуг", in = "formData", default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата, с которой услуга закрывается. ГГГГ-ММ-ДД. Если не указано - с начала следующего периода (точную дату см. в get-account-tariff/default_actual_from)", in = "formData", default = ""),
     *   @SWG\Parameter(name = "user_info", type = "string", description = "Информация о юзере (логин, IP, user-agent)", in = "formData", default = ""),
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
     * @return int
     * @throws Exception
     */
    public function actionCloseAccountTariff()
    {
        $postData = Yii::$app->request->post();
        return $this->editAccountTariff(
            isset($postData['account_tariff_ids']) ? $postData['account_tariff_ids'] : [],
            null,
            (isset($postData['actual_from']) && $postData['actual_from']) ? $postData['actual_from'] : null,
            isset($postData['user_info']) ? $postData['user_info'] : ''
        );
    }

    /**
     * @SWG\Post(tags = {"UniversalTariffs"}, path = "/internal/uu/cancel-edit-account-tariff", summary = "Отменить последнюю смену тарифа (или закрытие) услуги ЛС", operationId = "CancelEditAccountTariff",
     *   @SWG\Parameter(name = "account_tariff_ids[0]", type = "integer", description = "IDs услуг", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "account_tariff_ids[1]", type = "integer", description = "IDs услуг", in = "formData", default = ""),
     *   @SWG\Parameter(name = "user_info", type = "string", description = "Информация о юзере (логин, IP, user-agent)", in = "formData", default = ""),
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
     * @throws HttpException
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCancelEditAccountTariff()
    {
        $postData = Yii::$app->request->post();
        $account_tariff_ids = isset($postData['account_tariff_ids']) ? $postData['account_tariff_ids'] : [];

        if (!$account_tariff_ids || !is_array($account_tariff_ids)) {
            throw new HttpException(ModelValidationException::STATUS_CODE, 'Не указан обязательный параметр account_tariff_ids', AccountTariff::ERROR_CODE_USAGE_EMPTY);
        }

        foreach ($account_tariff_ids as $account_tariff_id) {

            $account_tariff_id = trim($account_tariff_id);
            $accountTariff = AccountTariff::findOne(['id' => (int)$account_tariff_id]);
            if (!$accountTariff) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Услуга с таким идентификатором не найдена', AccountTariff::ERROR_CODE_USAGE_EMPTY);
            }

            if (!$accountTariff->isLogCancelable()) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Нельзя отменить уже примененный тариф', AccountTariff::ERROR_CODE_USAGE_CANCELABLE);
            }

            // лог тарифов
            $accountTariffLogs = $accountTariff->accountTariffLogs;

            // отменяемый тариф
            /** @var AccountTariffLog $accountTariffLogCancelled */
            $accountTariffLogCancelled = array_shift($accountTariffLogs);
            $accountTariffLogCancelled->user_info = isset($postData['user_info']) ? $postData['user_info'] : '';
            if (!$accountTariff->isLogCancelable()) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Нельзя отменить уже примененный тариф', AccountTariff::ERROR_CODE_USAGE_CANCELABLE);
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
                $accountTariff->tariff_period_utc = DateTimeZoneHelper::getUtcDateTime()
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);
                if (!$accountTariff->save()) {
                    throw new ModelValidationException($accountTariff, $accountTariff->errorCode);
                }
            }
        }

        return true;
    }

    /**
     * @SWG\Definition(definition = "vpsRecord", type = "object",
     *   @SWG\Property(property = "vm_user_id", type = "string|null", description = "ID юзера в VPS manager (обычно не нужен)"),
     *   @SWG\Property(property = "vm_user_login", type = "string|null", description = "Логин юзера в VPS manager"),
     *   @SWG\Property(property = "vm_user_password", type = "string|null", description = "Постоянный пароль юзера в VPS manager (обычно не нужен)"),
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-vm-collocation-info", summary = "Информация о VPS ЛС", operationId = "GetVpsInfo",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Информация о VPS ЛС",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/vpsRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $client_account_id
     * @return array
     * @throws HttpException
     */
    public function actionGetVpsInfo($client_account_id = 0)
    {
        $client_account_id = (int)$client_account_id;
        if (!$client_account_id) {
            throw new HttpException(ModelValidationException::STATUS_CODE, 'Не указан client_account_id');
        }

        $account = ClientAccount::findOne(['id' => $client_account_id]);
        if (!$account) {
            throw new HttpException(ModelValidationException::STATUS_CODE, 'Несуществующий client_account_id ' . $client_account_id);
        }

        $syncVps = (new SyncVps);
        return [
            'vm_user_id' => $vm_user_id = $syncVps->getVmUserInfo($account, SyncVps::CLIENT_ACCOUNT_OPTION_VPS_ELID),
            'vm_user_login' => $vm_user_id ? ('client_' . $client_account_id) : null,
            'vm_user_password' => $syncVps->getVmUserInfo($account, SyncVps::CLIENT_ACCOUNT_OPTION_VPS_PASSWORD),
        ];
    }

    /**
     * @SWG\Definition(definition = "editAccountTariffResourceRecord", type = "object",
     *   @SWG\Property(property = "amount_use", type = "float", description = "Используемое количество ресурса"),
     *   @SWG\Property(property = "amount_free", type = "float", description = "Условно-бесплатное количество ресурса, включенное в тариф"),
     *   @SWG\Property(property = "amount_overhead", type = "float", description = "Количество ресурса сверх бесплатного (amount_use - amount_free)"),
     *   @SWG\Property(property = "price_per_unit", type = "float", description = "Цена за единицу ресурса в день"),
     *   @SWG\Property(property = "coefficient", type = "integer", description = "Количество дней"),
     *   @SWG\Property(property = "price", type = "float", description = "Стоимость этого ресурса за период, указанный ниже. Если null - списание невозможно"),
     *   @SWG\Property(property = "actual_from", type = "string", description = "Дата, с которой начинается действие и списание. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "actual_to", type = "string", description = "Дата, по которую списано (включительно). ГГГГ-ММ-ДД"),
     * ),
     *
     * @SWG\Post(tags = {"UniversalTariffs"}, path = "/internal/uu/edit-account-tariff-resource", summary = "Изменить количество ресурса в услуге ЛС", operationId = "EditAccountTariffResource",
     *   @SWG\Parameter(name = "account_tariff_id", type = "integer", description = "ID услуги", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "resource_id", type = "integer", description = "ID ресурса", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "amount", type = "integer", description = "Количество ресурса", in = "formData", required = true, default = ""),
     *   @SWG\Parameter(name = "actual_from", type = "string", description = "Дата смены. ГГГГ-ММ-ДД. Если не указано - сейчас", in = "formData", default = ""),
     *   @SWG\Parameter(name = "is_validate_only", type = "integer", description = "Эмуляция без реального действия. 0 - изменить количество ресурса и списать деньги. 1 - не менять и не списывать, но валидировать и посчитать стоимость", in = "formData", default = "0"),
     *   @SWG\Parameter(name = "user_info", type = "string", description = "Информация о юзере (логин, IP, user-agent)", in = "formData", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Количество ресурса изменено",
     *     @SWG\Schema(ref = "#/definitions/editAccountTariffResourceRecord")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @return array
     * @throws Exception
     */
    public function actionEditAccountTariffResource()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $post = Yii::$app->request->post();
        try {

            $isValidateOnly = (isset($post['is_validate_only']) && $post['is_validate_only']) ? (boolean)$post['is_validate_only'] : false;

            $accountTariffResourceLog = new AccountTariffResourceLog();
            $accountTariffResourceLog->setAttributes($post);
            if (!$accountTariffResourceLog->actual_from_utc) {
                $accountTariff = $accountTariffResourceLog->accountTariff;
                if (!$accountTariff || !$accountTariff->clientAccount) {
                    throw new InvalidParamException('Неправильная услуга');
                }

                $accountTariffResourceLog->actual_from_utc = (new \DateTime('00:00:00', $accountTariff->clientAccount->getTimezone()))
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);
            }

            if (
                ($isValidateOnly && !$accountTariffResourceLog->validate()) || // если is_validate_only, то только валидировать
                (!$isValidateOnly && !$accountTariffResourceLog->save()) // если !is_validate_only, то валидировать и сохранить
            ) {
                throw new ModelValidationException($accountTariffResourceLog, $accountTariffResourceLog->errorCode);
            }

            $accountLogResource = $accountTariffResourceLog->validatorBalance('id', []);
            $transaction->commit();

            return [
                'amount_use' => $accountLogResource ? $accountLogResource->amount_use : null,
                'amount_free' => $accountLogResource ? $accountLogResource->amount_free : null,
                'amount_overhead' => $accountLogResource ? $accountLogResource->amount_overhead : null,
                'price_per_unit' => $accountLogResource ? $accountLogResource->price_per_unit : null,
                'coefficient' => $accountLogResource ? $accountLogResource->coefficient : null,
                'price' => $accountLogResource ? $accountLogResource->price : null,
                'actual_from' => $accountLogResource ? $accountLogResource->date_from : null,
                'actual_to' => $accountLogResource ? $accountLogResource->date_to : null,
            ];

        } catch (Exception $e) {
            $transaction->rollBack();
            \Yii::error(
                print_r(['EditAccountTariffResource', $e->getMessage(), $post], true),
                Module::LOG_CATEGORY_API
            );
            throw $e;
        }
    }

    /**
     * @SWG\Definition(definition = "accountEntryRecord", type = "object",
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "date", type = "string", description = "Дата счета. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "date_from", type = "string", description = "Минимальная дата транзакций. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "date_to", type = "string", description = "Максимальная дата транзакций. ГГГГ-ММ-ДД"),
     *   @SWG\Property(property = "account_tariff_id", type = "integer", description = "ID услуги"),
     *   @SWG\Property(property = "tariff_period_id", type = "integer", description = "ID периода/тариф"),
     *   @SWG\Property(property = "type_id", type = "integer", description = "-1 - Подключение. -2 - Абонентка. -3 - Минималка. Положительное - Ресурс тарифа"),
     *   @SWG\Property(property = "price", type = "float", description = "Цена по тарифу, ¤"),
     *   @SWG\Property(property = "price_without_vat", type = "float", description = "Цена без НДС, ¤"),
     *   @SWG\Property(property = "vat_rate", type = "float", description = "НДС, %"),
     *   @SWG\Property(property = "vat", type = "float", description = "НДС, ¤"),
     *   @SWG\Property(property = "price_with_vat", type = "float", description = "Цена с НДС, ¤"),
     * ),
     *
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-account-entries", summary = "Список реалтайм проводок, еще не ставших бухгалтерскими", operationId = "GetAccountEntries",
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС", in = "query", required = true, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список реалтайм проводок, еще не ставших бухгалтерскими",
     *     @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/accountEntryRecord"))
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $client_account_id
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAccountEntries($client_account_id)
    {
        $query = Bill::getUnconvertedAccountEntries($client_account_id);

        $result = [];
        /** @var AccountEntry $accountEntry */
        foreach ($query->each() as $accountEntry) {
            $result[] = [
                'name' => $accountEntry->getFullName(),
                'date' => $accountEntry->date,
                'date_from' => $accountEntry->date_from,
                'date_to' => $accountEntry->date_to,
                'account_tariff_id' => $accountEntry->account_tariff_id,
                'tariff_period_id' => $accountEntry->tariff_period_id,
                'type_id' => $accountEntry->type_id,
                'price' => $accountEntry->price,
                'price_without_vat' => $accountEntry->price_without_vat,
                'vat_rate' => $accountEntry->vat_rate,
                'vat' => $accountEntry->vat,
                'price_with_vat' => $accountEntry->price_with_vat,
            ];
        }

        return $result;
    }

    /**
     * Проверить, что тариф разрешен для этого ЛС
     *
     * @param AccountTariff $accountTariff
     * @param AccountTariffLog $accountTariffLog
     * @throws HttpException
     */
    private function _checkTariff($accountTariff, $accountTariffLog)
    {
        if (!$accountTariffLog->tariff_period_id) {
            // закрыть можно
            // @todo дефолтный пакет закрыть нельзя
            return;
        }

        if ($accountTariffLog->tariffPeriod->tariff->isTest) {
            // тестовый можно подключать
            // @todo на самом деле можно не всем, а только "заказ услуг" и "подключаемый". А вот "включенным" нельзя
            return;
        }

        // проверить папку "публичный" (вернее, в соответствии с уровнем цен УЛС)
        $tariffs = $this->actionGetTariffs(
            $idTmp = null,
            $accountTariff->service_type_id,
            $countryIdTmp = null,
            $clientAccountIdTmp = null,
            $currencyIdTmp = null,
            $isDefaultTmp = null,
            $isPostpaidTmp = null,
            $is_one_active = null,
            $tariffStatusIdTmp = null,
            $tariffPersonIdTmp = null,
            $tariffTagIdTmp = null,
            $tariffTagsIdTmp = null,
            $voipGroupIdTmp = null,
            $voipCityIdTmp = null,
            $voipNdcTypeIdTmp = null,
            $organizationIdTmp = null,
            $voipNumberTmp = null,
            $accountTariffIdTmp = $accountTariff->id
        );

        foreach ($tariffs as $tariff) {
            foreach ($tariff['tariff_periods'] as $tariffPeriod) {
                if ($tariffPeriod['id'] == $accountTariffLog->tariff_period_id) {
                    return;
                }
            }
        }

        throw new HttpException(ModelValidationException::STATUS_CODE, 'Тариф недоступен этому ЛС', AccountTariff::ERROR_CODE_TARIFF_WRONG);
    }

    /**
     * @SWG\Get(tags = {"UniversalTariffs"}, path = "/internal/uu/get-service-types-by-contract", summary = "Список типов услуг на УЛС", operationId = "GetServiceTypesByContract",
     *   @SWG\Parameter(name = "contract_id", type = "integer", description = "ID договора (если несколько: то массив ID, или значение через ',')", in = "query", required = true, default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список типов услуг на УЛС",
     *     @SWG\Schema(type = "array")
     *   ),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     */
    /**
     * @param int $contract_id
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetServiceTypesByContract($contract_id = null)
    {
        if (!$contract_id) {
            throw new InvalidArgumentException('ContractId(s) not set');
        }

        if (!is_array($contract_id)) {
            $contract_id = explode(',', $contract_id);
        }

        $contract_id = array_filter(array_map('trim', $contract_id));

        $query = ClientAccount::find()
            ->alias('c')
            ->joinWith('accountTariffs at', true, 'INNER JOIN')
            ->select(['at.service_type_id', 'at.client_account_id', 'contract_id'])
            ->distinct()
            ->orderBy(['contract_id' => SORT_ASC, 'client_account_id' => SORT_ASC, 'service_type_id' => SORT_ASC])
            ->asArray()
            ->where(['contract_id' => $contract_id]);

        $result = [];

        foreach ($query->each() as $v) {
            if (!isset($result[$v['contract_id']])) {
                $result[$v['contract_id']] = [
                    'contract_id' => $v['contract_id'],
                    'accounts' => []
                ];
            }
            if (!isset($result[$v['contract_id']]['accounts'][$v['client_account_id']])) {
                $result[$v['contract_id']]['accounts'][$v['client_account_id']] = [
                    'account_id' => $v['client_account_id'],
                    'service_type_ids' => []
                ];
            }
            $result[$v['contract_id']]['accounts'][$v['client_account_id']]['service_type_ids'][] = $v['service_type_id'];
        }


        return $result;
    }

}