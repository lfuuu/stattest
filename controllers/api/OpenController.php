<?php

namespace app\controllers\api;

use app\exceptions\ModelValidationException;
use app\models\City;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\Currency;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\PackagePrice;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Period;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffStatus;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipCountry;
use app\modules\uu\models\TariffVoipGroup;
use app\modules\uu\models\TariffVoipNdcType;
use HttpException;
use Yii;
use yii\web\Controller;

final class OpenController extends Controller
{
    public $enableCsrfValidation = false;

    private $_defaultTariffCache = [];

    /**
     * Инициализация
     */
    public function init()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * @SWG\Definition(definition = "voipDefaultPackageRecord", type = "object",
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "tariff_period_id", type = "integer", description = "ID тарифа/периода"),
     *   @SWG\Property(property = "price_setup", type = "float", description = "Стоимость подключения тарифа. Надо добавлять к стоимости подключения самого номера"),
     *   @SWG\Property(property = "price_per_period", type = "float", description = "Абонентская плата за месяц"),
     *   @SWG\Property(property = "price_min", type = "float", description = "Минимальная плата за месяц"),
     *   @SWG\Property(property = "cost_per_period", type = "float", description = "Абонентская и минимальная плата до конца текущего месяца"),
     *   @SWG\Property(property = "lines", type = "integer", description = "Количество линий, включенных в пакет"),
     *   @SWG\Property(property = "line_price", type = "float", description = "Плата за доп. канал за месяц"),
     *   @SWG\Property(property = "call_price_mobile", type = "float", description = "Цена звонков на сотовые за минуту"),
     *   @SWG\Property(property = "call_price_local", type = "float", description = "Цена звонков на местные за минуту"),
     * ),
     *
     * @SWG\Definition(definition = "voipCallsPerMonthRecord", type = "object",
     *   @SWG\Property(property = "11", type = "integer", description = "Кол-во звонков за этот месяц"),
     *   @SWG\Property(property = "10", type = "integer", description = "Кол-во звонков за предыдущий месяц"),
     *   @SWG\Property(property = "09", type = "integer", description = "Кол-во звонков за месяц перед предыдущим месяц"),
     * ),
     *
     * @SWG\Definition(definition = "freeNumberRecord", type = "object",
     *   @SWG\Property(property = "number", type = "string", description = "Номер"),
     *   @SWG\Property(property = "beauty_level", type = "integer", description = "Уровень красивости (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)"),
     *   @SWG\Property(property = "price", type = "integer", description = "Цена"),
     *   @SWG\Property(property = "currency", type = "string", description = "Код валюты (ISO)"),
     *   @SWG\Property(property = "origin_price", type = "integer", description = "Исходная цена"),
     *   @SWG\Property(property = "origin_currency", type = "string", description = "Исходный код валюты (ISO)"),
     *   @SWG\Property(property = "region", type = "integer", description = "ID региона"),
     *   @SWG\Property(property = "city_id", type = "integer", description = "ID города"),
     *   @SWG\Property(property = "did_group_id", type = "integer", description = "ID DID-группы"),
     *   @SWG\Property(property = "ndc_type_id", type = "integer", description = "ID типа номера"),
     *   @SWG\Property(property = "country_prefix", type = "integer", description = "Префикс страны"),
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC"),
     *   @SWG\Property(property = "number_subscriber", type = "integer", description = "Номер без префикса и NDC"),
     *   @SWG\Property(property = "common_ndc", type = "integer", description = "Общепринятый NDC"),
     *   @SWG\Property(property = "common_number_subscriber", type = "integer", description = "Общепринятый местный номер"),
     *   @SWG\Property(property = "default_tariff", type = "object", description = "Дефолтный пакет", ref = "#/definitions/voipDefaultPackageRecord"),
     *   @SWG\Property(property = "calls_per_month", type = "object", description = "Дефолтный пакет", ref = "#/definitions/voipCallsPerMonthRecord")
     * ),
     *
     * @SWG\Get(tags = {"Numbers"}, path = "/open/get-free-numbers", summary = "Список свободных номеров", operationId = "getFreeNumbers",
     *   @SWG\Parameter(name = "regions[0]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "regions[1]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndcType", type = "integer", description = "Тип номеров", in = "query", default = ""),
     *   @SWG\Parameter(name = "minCost", type = "number", description = "Минимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "maxCost", type = "number", description = "Максимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "beautyLvl", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query", default = ""),
     *   @SWG\Parameter(name = "like", type = "string", description = "Маска номера телефона. Синтаксис: '.' - один символ, '*' - любое кол-во символов", in = "query", default = ""),
     *   @SWG\Parameter(name = "mask", type = "string", description = "Маска номера телефона. Допустимы [A-Z0-9*]", in = "query", default = ""),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Смещение результатов поиска", in = "query", default = ""),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Кол-во записей", in = "query", default = "12"),
     *   @SWG\Parameter(name = "currency", type = "string", description = "Код валюты (ISO)", in = "query", default = ""),
     *   @SWG\Parameter(name = "countryCode", type = "integer", description = "Код страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[0]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[1]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "similar", type = "string", description = "Значение для подсчета схожести", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[0]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[1]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС (для определения по нему страны, валюты, тарифа и пр.)", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список свободных номеров", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/freeNumberRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param array $regions
     * @param int $ndcType
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
     * @param int $client_account_id
     * @param int $isShowInLkLevel
     * @return array
     * @throws HttpException
     */
    public function actionGetFreeNumbers(
        array $regions = [],
        $ndcType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $mask = null,
        $offset = 0,
        $limit = FreeNumberFilter::LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $similar = null,
        $ndc = null,
        array $excludeNdcs = [],
        $client_account_id = null,
        $isShowInLkLevel = City::IS_SHOW_IN_LK_FULL
    )
    {

        \Yii::info(
            print_r([
                'actionGetFreeNumbers',
                $regions,
                $ndcType,
                $minCost,
                $maxCost,
                $beautyLvl,
                $like,
                $mask,
                $offset,
                $limit,
                $currency,
                $countryCode,
                $cities,
                $similar,
                $ndc,
                $excludeNdcs,
                $client_account_id
            ], true),
            \app\modules\uu\Module::LOG_CATEGORY_API
        );

        $numbers = (new FreeNumberFilter)
            ->setIsService(false)
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
            ->setNdcType($ndcType)
            ->setOffset($offset)
            ->setLimit($limit)
            ->setIsShowInLk($isShowInLkLevel)
            ->orderBy(['number' => SORT_ASC]);

        $client_account_id = (int)$client_account_id;
        if ($client_account_id) {
            // взять страну от ЛС
            $clientAccount = ClientAccount::findOne(['id' => $client_account_id]);
            if (!$clientAccount) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный client_account_id', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
            }

            $priceLevel = $clientAccount->price_level;
            !$countryCode && $countryCode = $clientAccount->country_id;
            $currency = $clientAccount->currency;
            $isPostpaid = $clientAccount->is_postpaid;
            $tariffPersonId = ($clientAccount->contragent->legal_type == ClientContragent::PERSON_TYPE) ?
                TariffPerson::ID_NATURAL_PERSON :
                TariffPerson::ID_LEGAL_PERSON;

            $tariffCountryCode = $clientAccount->superClient->entryPoint ?
                $clientAccount->superClient->entryPoint->country_id :
                $clientAccount->contragent->country_id;

        } else {
            $clientAccount = null;
            $priceLevel = ClientAccount::DEFAULT_PRICE_LEVEL;
            $isPostpaid = false;
            $tariffPersonId = TariffPerson::ID_LEGAL_PERSON;
            $tariffCountryCode = null;
        }

        $responseNumbers = [];

        foreach ($numbers->result() as $freeNumber) {
            $responseNumber = $numbers->formattedNumber($freeNumber, $currency, $clientAccount);

            $didGroup = $freeNumber->getCachedDidGroup();

            $tariffStatusId = $didGroup->getTariffStatusMain($priceLevel);
            $packageStatusIds = [
                $didGroup->getTariffStatusPackage($priceLevel)
            ];

            if ($priceLevel >= DidGroup::MIN_PRICE_LEVEL_FOR_BEAUTY) {
                // только для ОТТ (см. ClientAccount::getPriceLevels)
                $packageStatusIds[] = $didGroup->tariff_status_beauty; // пакет за красивость
            }

            $responseNumber->default_tariff = $this->_getDefaultTariff(
                $tariffStatusId,
                $packageStatusIds,
                $freeNumber->city_id,
                $countryCode ?: $freeNumber->country_code,
                $tariffCountryCode,
                $currency ?: $freeNumber->country->currency_id,
                $isPostpaid,
                $tariffPersonId,
                $freeNumber->ndc_type_id
            );
            $responseNumbers[] = $responseNumber;
        }

        return [
            'total' => $numbers->count(),
            'numbers' => $responseNumbers,
        ];
    }

    /**
     *
     * @SWG\Get(tags = {"Numbers"}, path = "/open/get-free-numbers__for-api-mcn-ru", summary = "Список свободных номеров (для api.mcn.ru)", operationId = "getFreeNumbersForApiMcnRu",
     *   @SWG\Parameter(name = "regions[0]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "regions[1]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndcType", type = "integer", description = "Тип номеров", in = "query", default = ""),
     *   @SWG\Parameter(name = "minCost", type = "number", description = "Минимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "maxCost", type = "number", description = "Максимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "beautyLvl", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query", default = ""),
     *   @SWG\Parameter(name = "like", type = "string", description = "Маска номера телефона. Синтаксис: '.' - один символ, '*' - любое кол-во символов", in = "query", default = ""),
     *   @SWG\Parameter(name = "mask", type = "string", description = "Маска номера телефона. Допустимы [A-Z0-9*]", in = "query", default = ""),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Смещение результатов поиска", in = "query", default = ""),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Кол-во записей", in = "query", default = "12"),
     *   @SWG\Parameter(name = "currency", type = "string", description = "Код валюты (ISO)", in = "query", default = ""),
     *   @SWG\Parameter(name = "countryCode", type = "integer", description = "Код страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[0]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[1]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "similar", type = "string", description = "Значение для подсчета схожести", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[0]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[1]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС (для определения по нему страны, валюты, тарифа и пр.)", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список свободных номеров", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/freeNumberRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param array $regions
     * @param int $ndcType
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
     * @param int $client_account_id
     * @param int $isShowInLkLevel
     * @return array
     * @throws HttpException
     */
    public function actionGetFreeNumbers__forApiMcnRu(
        array $regions = [],
        $ndcType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $mask = null,
        $offset = 0,
        $limit = FreeNumberFilter::LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $similar = null,
        $ndc = null,
        array $excludeNdcs = [],
        $client_account_id = null,
        $isShowInLkLevel = City::IS_SHOW_IN_LK_API_ONLY


    )
    {
        return $this->actionGetFreeNumbers(
            $regions,
            $ndcType,
            $minCost,
            $maxCost,
            $beautyLvl,
            $like,
            $mask,
            $offset,
            $limit,
            $currency,
            $countryCode,
            $cities,
            $similar,
            $ndc,
            $excludeNdcs,
            $client_account_id,
            $isShowInLkLevel
        );
    }

    /**
     * @SWG\Definition(definition = "groupedFreeNumberRecord", type = "object",
     *   @SWG\Property(property = "beauty_level", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)"),
     *   @SWG\Property(property = "price", type = "integer", description = "Цена"),
     *   @SWG\Property(property = "currency", type = "string", description = "Код валюты (ISO)"),
     *   @SWG\Property(property = "origin_price", type = "integer", description = "Исходная цена"),
     *   @SWG\Property(property = "origin_currency", type = "string", description = "Исходный код валюты (ISO)"),
     *   @SWG\Property(property = "region", type = "integer", description = "ID региона"),
     *   @SWG\Property(property = "city_id", type = "integer", description = "ID города"),
     *   @SWG\Property(property = "did_group_id", type = "integer", description = "ID DID-группы"),
     *   @SWG\Property(property = "ndc_type_id", type = "integer", description = "ID типа номера"),
     *   @SWG\Property(property = "country_prefix", type = "integer", description = "Префикс страны"),
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC"),
     *   @SWG\Property(property = "common_ndc", type = "integer", description = "Общепринятый NDC"),
     *   @SWG\Property(property = "default_tariff", type = "object", description = "Дефолтный пакет", ref = "#/definitions/voipDefaultPackageRecord"),
     *   @SWG\Property(property = "numbers", type = "array", description = "NDC", @SWG\Items(type = "string"))
     * ),
     *
     * @SWG\Get(tags = {"Numbers"}, path = "/open/get-grouped-free-numbers", summary = "Список свободных номеров, сгруппированых по DID-группе", operationId = "getGroupedFreeNumbers",
     *   @SWG\Parameter(name = "regions[0]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "regions[1]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndcType", type = "integer", description = "Тип номеров", in = "query", default = ""),
     *   @SWG\Parameter(name = "minCost", type = "number", description = "Минимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "maxCost", type = "number", description = "Максимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "beautyLvl", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query", default = ""),
     *   @SWG\Parameter(name = "like", type = "string", description = "Маска номера телефона. Синтаксис: '.' - один символ, '*' - любое кол-во символов", in = "query", default = ""),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Кол-во записей во всей выборке", in = "query", default = "10000"),
     *   @SWG\Parameter(name = "limitPerGroup", type = "integer", description = "Кол-во записей в группе", in = "query", default = "100"),
     *   @SWG\Parameter(name = "currency", type = "string", description = "Код валюты (ISO)", in = "query", default = ""),
     *   @SWG\Parameter(name = "countryCode", type = "integer", description = "Код страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[0]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[1]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[0]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[1]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС (для определения по нему страны, валюты, тарифа и пр.)", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список свободных номеров, сгруппированых по DID-группе", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/groupedFreeNumberRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param array $regions
     * @param int $ndcType
     * @param float $minCost
     * @param float $maxCost
     * @param int $beautyLvl
     * @param string $like
     * @param int $limit
     * @param int $limitPerGroup
     * @param string $currency
     * @param int $countryCode
     * @param array $cities
     * @param int $ndc
     * @param int|int[] $excludeNdcs
     * @param int $client_account_id
     * @param int $isShowInLkLevel
     * @return array
     * @throws HttpException
     */
    public function actionGetGroupedFreeNumbers(
        array $regions = [],
        $ndcType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $limit = FreeNumberFilter::NO_LIMIT,
        $limitPerGroup = FreeNumberFilter::NO_LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $ndc = null,
        array $excludeNdcs = [],
        $client_account_id = null,
        $isShowInLkLevel = City::IS_SHOW_IN_LK_FULL
    )
    {
        $numbers = (new FreeNumberFilter)
            ->setIsService(false)
            ->setRegions($regions)
            ->setCountry($countryCode)
            ->setCities($cities)
            ->setMinCost($minCost)
            ->setMaxCost($maxCost)
            ->setBeautyLvl($beautyLvl)
            ->setNumberLike($like)
            // ->setNumberMask($mask)
            // ->setSimilar($similar)
            ->setNdc($ndc)
            ->setExcludeNdcs($excludeNdcs)
            ->setNdcType($ndcType)
            // ->setOffset($offset)
            ->setLimit($limit, FreeNumberFilter::NO_LIMIT)
            ->setIsShowInLk($isShowInLkLevel)
            ->orderBy(['number' => SORT_ASC]);

        $client_account_id = (int)$client_account_id;
        if ($client_account_id) {
            // взять страну от ЛС
            $clientAccount = ClientAccount::findOne(['id' => $client_account_id]);
            if (!$clientAccount) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный client_account_id', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
            }

            $priceLevel = $clientAccount->price_level;
            $countryId = $clientAccount->country_id;
            $currencyId = $clientAccount->country->currency_id;
            $isPostpaid = $clientAccount->is_postpaid;
            $tariffPersonId = ($clientAccount->contragent->legal_type == ClientContragent::PERSON_TYPE) ?
                TariffPerson::ID_NATURAL_PERSON :
                TariffPerson::ID_LEGAL_PERSON;

            $tariffCountryCode = $clientAccount->superClient->entryPoint ?
                $clientAccount->superClient->entryPoint->country_id :
                $clientAccount->contragent->country_id;


        } else {
            $clientAccount = null;
            $priceLevel = ClientAccount::DEFAULT_PRICE_LEVEL;
            $countryId = null;
            $currencyId = null;
            $isPostpaid = false;
            $tariffPersonId = TariffPerson::ID_LEGAL_PERSON;
            $tariffCountryCode = null;
        }

        $response = [];

        foreach ($numbers->resultF() as $_freeNumber) {

            $groupKey = $_freeNumber['city_id'] . '_' . $_freeNumber['did_group_id'];

            if ($limitPerGroup && count($response[$groupKey]['numbers']) >= $limitPerGroup) {
                continue;
            }

            if (isset($response[$groupKey])) {
                // добавить номер в существующую группу
                $response[$groupKey]['numbers'][] = $_freeNumber['number'];
                continue;
            }

            $freeNumber = Number::findOne(['number' => $_freeNumber['number']]);

            $tariffStatusId = TariffStatus::ID_TEST;
            $packageStatusIds = [
                TariffStatus::ID_PUBLIC,
            ];

            // создать новую группу
            // для reuse берем другой метод и выкидываем ненужное
            $responseNumber = $numbers->formattedNumber($freeNumber, $currency, $clientAccount, false);

            if ($clientAccount) {
                /** @var DidGroup $didGroup */
                $didGroup = $freeNumber->getCachedDidGroup();

                $tariffStatusId = $didGroup->getTariffStatusMain($priceLevel);
                $packageStatusIds = [
                    $didGroup->getTariffStatusPackage($priceLevel),
                ];
            }

            if ($priceLevel >= DidGroup::MIN_PRICE_LEVEL_FOR_BEAUTY) {
                // только для ОТТ (см. ClientAccount::getPriceLevels)
                $packageStatusIds[] = $didGroup->tariff_status_beauty; // пакет за красивость
            }

            !$countryId && $countryId = $freeNumber->country_code;
            !$currencyId && $currencyId = $freeNumber->getCachedCountry()->currency_id;

            $responseNumber->default_tariff = $this->_getDefaultTariff(
                $tariffStatusId,
                $packageStatusIds,
                $freeNumber->city_id,
                $countryId,
                $tariffCountryCode,
                $currencyId,
                $isPostpaid,
                $tariffPersonId,
                $freeNumber->ndc_type_id
            );

            $response[$groupKey] = [];
            foreach ($responseNumber as $key => $value) {
                if (in_array($key, ['number', 'number_subscriber', 'common_number_subscriber'], true)) {
                    continue;
                }

                $response[$groupKey][$key] = $value;
            }

            $response[$groupKey]['numbers'] = [(string)$freeNumber->number];
        }

        return $response;
    }

    /**
     * @SWG\Get(tags = {"Numbers"}, path = "/open/get-grouped-free-numbers__for-api-mcn-ru", summary = "Список свободных номеров, сгруппированых по DID-группе (для api.mcn.ru)", operationId = "getGroupedFreeNumbers__forApiMcnRu",
     *   @SWG\Parameter(name = "regions[0]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "regions[1]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndcType", type = "integer", description = "Тип номеров", in = "query", default = ""),
     *   @SWG\Parameter(name = "minCost", type = "number", description = "Минимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "maxCost", type = "number", description = "Максимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "beautyLvl", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query", default = ""),
     *   @SWG\Parameter(name = "like", type = "string", description = "Маска номера телефона. Синтаксис: '.' - один символ, '*' - любое кол-во символов", in = "query", default = ""),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Кол-во записей во всей выборке", in = "query", default = "10000"),
     *   @SWG\Parameter(name = "limitPerGroup", type = "integer", description = "Кол-во записей в группе", in = "query", default = "100"),
     *   @SWG\Parameter(name = "currency", type = "string", description = "Код валюты (ISO)", in = "query", default = ""),
     *   @SWG\Parameter(name = "countryCode", type = "integer", description = "Код страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[0]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[1]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[0]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[1]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС (для определения по нему страны, валюты, тарифа и пр.)", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список свободных номеров, сгруппированых по DID-группе", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/groupedFreeNumberRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param array $regions
     * @param int $ndcType
     * @param float $minCost
     * @param float $maxCost
     * @param int $beautyLvl
     * @param string $like
     * @param int $limit
     * @param int $limitPerGroup
     * @param string $currency
     * @param int $countryCode
     * @param array $cities
     * @param int $ndc
     * @param int|int[] $excludeNdcs
     * @param int $client_account_id
     * @return array
     * @throws \HttpException
     */
    public function actionGetGroupedFreeNumbers__forApiMcnRu(
        array $regions = [],
        $ndcType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $limit = FreeNumberFilter::NO_LIMIT,
        $limitPerGroup = FreeNumberFilter::NO_LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $ndc = null,
        array $excludeNdcs = [],
        $client_account_id = null
    )
    {
        return $this->actionGetGroupedFreeNumbers(
            $regions,
            $ndcType,
            $minCost,
            $maxCost,
            $beautyLvl,
            $like,
            $limit,
            $limitPerGroup,
            $currency,
            $countryCode,
            $cities,
            $ndc,
            $excludeNdcs,
            $client_account_id,
            City::IS_SHOW_IN_LK_API_ONLY
        );
    }

    /**
     * @param int $tariffStatusId
     * @param int[] $packageStatusIds
     * @param int $voipCityId
     * @param int $voipCountryId
     * @param int $tariffCountryId
     * @param int $currencyId
     * @param int $isPostpaid
     * @param int $tariffPersonId
     * @param int $ndcTypeId
     * @return array
     */
    private function _getDefaultTariff(
        $tariffStatusId,
        $packageStatusIds,
        $voipCityId,
        $voipCountryId,
        $tariffCountryId,
        $currencyId,
        $isPostpaid,
        $tariffPersonId,
        $ndcTypeId
    )
    {
        $tariffStatusIdKey = $tariffStatusId . '_' . implode('_', $packageStatusIds) . '_' . $ndcTypeId;
        if (isset($this->_defaultTariffCache[$tariffStatusIdKey])) {
            // взять из кэша
            return $this->_defaultTariffCache[$tariffStatusIdKey];
        }

        $isDefault = true;
        $serviceTypeId = ServiceType::ID_VOIP;

        $tariffQuery = Tariff::find()
            ->joinWith('tariffVoipCountries')
            ->andWhere([TariffVoipCountry::tableName() . '.country_id' => (int)$voipCountryId]);

        if ($tariffCountryId) {
            $tariffQuery
                ->joinWith('tariffCountries')
                ->andWhere([TariffCountry::tableName() . '.country_id' => (int)$tariffCountryId]);
        }
        $tariffTableName = Tariff::tableName();

        $serviceTypeId && $tariffQuery->andWhere([$tariffTableName . '.service_type_id' => (int)$serviceTypeId]);
        $currencyId && $tariffQuery->andWhere([$tariffTableName . '.currency_id' => $currencyId]);
        !is_null($isDefault) && $tariffQuery->andWhere([$tariffTableName . '.is_default' => (int)$isDefault]);
        !is_null($isPostpaid) && $tariffQuery->andWhere([$tariffTableName . '.is_postpaid' => (int)$isPostpaid]);
        $tariffStatusId && $tariffQuery->andWhere([$tariffTableName . '.tariff_status_id' => (int)$tariffStatusId]);
        $tariffPersonId && $tariffQuery->andWhere([$tariffTableName . '.tariff_person_id' => [TariffPerson::ID_ALL, $tariffPersonId]]);

        if ($voipCityId) {
            $tariffQuery
                ->joinWith('voipCities')
                ->andWhere([
                    'OR',
                    [TariffVoipCity::tableName() . '.city_id' => $voipCityId], // если в тарифе хоть один город, то надо только точное соотвествие
                    [TariffVoipCity::tableName() . '.city_id' => null] // если в тарифе ни одного города нет, то это означает "любой город этой страны"
                ]);
        }

        if ($serviceTypeId == ServiceType::ID_VOIP && $ndcTypeId) {
            $tariffQuery
                ->joinWith('voipNdcTypes')
                ->andWhere([TariffVoipNdcType::tableName() . '.ndc_type_id' => $ndcTypeId]);
        }

        /** @var Tariff $tariff */
        $tariff = $tariffQuery->one();
        if (!$tariff) {
            return $this->_defaultTariffCache[$tariffStatusIdKey] = [];
        }

        $tariffPeriods = $tariff->tariffPeriods;
        $tariffPeriod = null;
        foreach ($tariffPeriods as $tariffPeriodTmp) {

            if (!$tariffPeriod) {
                // хоть что-нибудь
                $tariffPeriod = $tariffPeriodTmp;
            }

            if ($tariffPeriodTmp->charge_period_id == Period::ID_MONTH) {
                // по умолчанию - помесячный, если есть
                $tariffPeriod = $tariffPeriodTmp;
                break;
            }
        }

        /** @var TariffResource $tariffResources */
        $tariffResources = $tariff->getTariffResource(ResourceModel::ID_VOIP_LINE)->one();

        $defaultTariff = [
            'name' => $tariff->name,
            'tariff_period_id' => $tariffPeriod->id,
            'price_setup' => $tariffPeriod->price_setup,
            'price_per_period' => $tariffPeriod->price_per_period,
            'price_min' => $tariffPeriod->price_min,
            'cost_per_period' => null,
            'lines' => $tariffResources->amount,
            'line_price' => $tariffResources->price_per_unit,
            'call_price_mobile' => null,
            'call_price_local' => null,
        ];

        // дефолтные пакеты, в том числе и за красивость
        $tariffTableName = Tariff::tableName();
        $tariffPackagesQuery = Tariff::find()
            ->joinWith('voipNdcTypes')
            ->joinWith('tariffVoipCountries')
            ->where([
                $tariffTableName . '.service_type_id' => array_keys(ServiceType::$packages),
                $tariffTableName . '.currency_id' => $tariff->currency_id,
                $tariffTableName . '.is_default' => 1,
                $tariffTableName . '.tariff_status_id' => $packageStatusIds,
                $tariffTableName . '.tariff_person_id' => [TariffPerson::ID_ALL, $tariffPersonId],
                TariffVoipNdcType::tableName() . '.ndc_type_id' => $ndcTypeId,
            ]);

        if ($tariffCountryId) {
            $tariffPackagesQuery
                ->joinWith('tariffCountries')
                ->andWhere([TariffCountry::tableName() . '.country_id' => (int)$tariffCountryId]);
        }

        if ($voipCityId) {
            $tariffPackagesQuery
                ->joinWith('voipCities')
                ->andWhere([
                    'OR',
                    [TariffVoipCity::tableName() . '.city_id' => $voipCityId], // если в тарифе хоть один город, то надо только точное соотвествие
                    [TariffVoipCity::tableName() . '.city_id' => null] // если в тарифе ни одного города нет, то это означает "любой город этой страны"
                ]);
        }

        /** @var Tariff $tariffPackage */
        foreach ($tariffPackagesQuery->each() as $tariffPackage) {

            if ($tariffPackage->voip_group_id === TariffVoipGroup::ID_LOCAL) {
                // стоимость звонков
                $packagePrices = $tariffPackage ? $tariffPackage->getPackagePrices()->with('destination')->all() : [];
                foreach ($packagePrices as $packagePrice) {
                    /** @var PackagePrice $packagePrice */
                    $destination = $packagePrice->destination;
                    if ($destination->isLocal()) {
                        $defaultTariff['call_price_local'] = $packagePrice->price;
                    } elseif ($destination->isMobile()) {
                        $defaultTariff['call_price_mobile'] = $packagePrice->price;
                    }
                }
            }

            // абонентка и минималка
            $tariffPackagePeriods = $tariffPackage->tariffPeriods;
            $tariffPackagePeriod = reset($tariffPackagePeriods);
            $defaultTariff['price_setup'] += $tariffPackagePeriod->price_setup;
            $defaultTariff['price_per_period'] += $tariffPackagePeriod->price_per_period;
            $defaultTariff['price_min'] += $tariffPackagePeriod->price_min;
        }

        // Абонентская и минимальная плата до конца текущего месяца
        $dateTime = new \DateTimeImmutable();
        $daysInMonth = (int)$dateTime->format('t');
        $currentDay = (int)$dateTime->format('j');
        $daysLeft = $daysInMonth - $currentDay + 1; // "+1", потому что текущий день тоже надо считать
        $coefficient = $daysLeft / $daysInMonth;
        $defaultTariff['cost_per_period'] = ($defaultTariff['price_per_period'] + $defaultTariff['price_min']) * $coefficient;

        return $this->_defaultTariffCache[$tariffStatusIdKey] = $defaultTariff;
    }

    /**
     * @SWG\Definition(definition = "did_group", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор группы"),
     *   @SWG\Property(property = "name", type = "string", description = "Наименование группы"),
     *   @SWG\Property(property = "country_code", type = "integer", description = "Идентификатор страны"),
     *   @SWG\Property(property = "city_id", type = "integer", description = "Идентификатор города"),
     *   @SWG\Property(property = "beauty_level", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)"),
     *   @SWG\Property(property = "ndc_type_id", type = "integer", description = "Тип номеров")
     * ),
     *
     * @SWG\Get(tags = {"Numbers"}, path = "/open/did-groups", summary = "Список DID групп", operationId = "didGroups",
     *   @SWG\Parameter(name = "id[0]", type = "integer", description = "идентификатор(ы) DID групп", in = "query", default = "", ),
     *   @SWG\Parameter(name = "id[1]", type = "integer", description = "идентификатор(ы) DID групп", in = "query", default = ""),
     *   @SWG\Parameter(name = "beautyLvl[0]", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query"),
     *   @SWG\Parameter(name = "beautyLvl[1]", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query"),
     *
     *   @SWG\Response(response = 200, description = "Список DID групп", @SWG\Definition(ref = "#/definitions/did_group")),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param int[] $id
     * @param int[] $beautyLvl
     * @return DidGroup[]
     */
    public function actionDidGroups(array $id = [], array $beautyLvl = [])
    {
        $result = DidGroup::find()
            ->where(['is_service' => 0]);

        if (count($id)) {
            $result->andWhere(['IN', 'id', $id]);
        }

        if (count($beautyLvl)) {
            $result->andWhere(['IN', 'beauty_level', $beautyLvl]);
        }

        return $result->all();
    }
}