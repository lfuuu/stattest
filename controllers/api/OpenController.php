<?php

namespace app\controllers\api;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\Currency;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffVoipCity;
use HttpException;
use Yii;
use yii\web\Controller;

final class OpenController extends Controller
{

    const FREE_NUMBERS_PREVIEW_MODE = 4;

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
     * @SWG\Get(tags = {"Numbers"}, path = "/open/get-free-numbers", summary = "Выбрать список свободных номеров по одному региону", operationId = "getFreeNumbers",
     *   @SWG\Parameter(name = "region", type = "integer", description = "код региона", in = "query", default = ""),
     *   @SWG\Parameter(name = "currency", type = "string", description = "код валюты (ISO)", in = "query", default = ""),
     *   @SWG\Response(response = 200, description = "Выбрать список свободных номеров", @SWG\Items(ref = "#/definitions/freeNumberRecord")),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param int $region
     * @param string $currency
     * @return array
     */
    public function actionGetFreeNumbers($region = null, $currency = Currency::RUB)
    {
        $numbers = (new FreeNumberFilter)
            ->setNdcType(NdcType::ID_GEOGRAPHIC)
            ->setIsService(false)
            ->setRegions([$region]);

        $response = [];
        foreach ($numbers->result(null) as $row) {
            $response[] = $numbers->formattedNumber($row, $currency);
        }

        return $response;
    }

    /**
     * @SWG\Definition(definition = "freeNumberRecords", type = "object",
     *   @SWG\Property(property = "total", type = "int", description = "Всего номеров, удовлетворяющих условиям запроса без limit/offset"),
     *   @SWG\Property(property = "numbers", type = "array", description = "Номер", @SWG\Items(ref = "#/definitions/freeNumberRecord")),
     * ),
     *
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
     * @SWG\Definition(definition = "freeNumberRecord", type = "object",
     *   @SWG\Property(property = "number", type = "string", description = "Номер"),
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
     *   @SWG\Property(property = "number_subscriber", type = "integer", description = "Номер без префикса и NDC"),
     *   @SWG\Property(property = "common_ndc", type = "integer", description = "Общепринятый NDC"),
     *   @SWG\Property(property = "common_number_subscriber", type = "integer", description = "Общепринятый местный номер"),
     *   @SWG\Property(property = "default_tariff", type = "object", description = "Дефолтный пакет", ref = "#/definitions/voipDefaultPackageRecord")
     * ),
     *
     * @SWG\Get(tags = {"Numbers"}, path = "/open/get-free-numbers-by-filter", summary = "Выбрать список свободных номеров", operationId = "getFreeNumbersByFilter",
     *   @SWG\Parameter(name = "regions[0]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "regions[1]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndcType", type = "integer", description = "Тип номеров", in = "query", default = ""),
     *   @SWG\Parameter(name = "minCost", type = "number", description = "Минимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "maxCost", type = "number", description = "Максимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "beautyLvl", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query", default = ""),
     *   @SWG\Parameter(name = "like", type = "string", description = "Маска номера телефона. Синтахис: '.' - один символ, '*' - любое кол-во символов", in = "query", default = ""),
     *   @SWG\Parameter(name = "mask", type = "string", description = "Маска номера телефона. Допустимы [A-Z0-9*]", in = "query", default = ""),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Смещение результатов поиска", in = "query", default = ""),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Кол-во записей (default: 12, 'null' для получения всех)", in = "query", default = ""),
     *   @SWG\Parameter(name = "currency", type = "string", description = "Код валюты (ISO)", in = "query", default = ""),
     *   @SWG\Parameter(name = "countryCode", type = "integer", description = "Код страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[0]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[1]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "similar", type = "string", description = "Значение для подсчета схожести", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[0]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[1]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "excludeNdcs[1]", type = "integer", description = "Кроме NDC", in = "query", default = ""),
     *   @SWG\Parameter(name = "client_account_id", type = "integer", description = "ID ЛС (для определения по нему страны, валюты, тарифа и пр.)", in = "query", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Выбрать список свободных номеров", @SWG\Definition(ref = "#/definitions/freeNumberRecords")),
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
     * @return array
     * @throws \HttpException
     */
    public function actionGetFreeNumbersByFilter(
        array $regions = [],
        $ndcType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $mask = null,
        $offset = 0,
        $limit = FreeNumberFilter::FREE_NUMBERS_LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $similar = null,
        $ndc = null,
        array $excludeNdcs = [],
        $client_account_id = null
    ) {
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
            ->orderBy(['number' => SORT_ASC]);

        if ((int)$offset) {
            $numbers->setOffset((int)$offset);
        }

        if ((int)$ndcType) {
            $numbers->setNdcType((int)$ndcType);
        }

        $client_account_id = (int)$client_account_id;
        if ($client_account_id) {
            // взять страну от ЛС
            $clientAccount = ClientAccount::findOne(['id' => $client_account_id]);
            if (!$clientAccount) {
                throw new HttpException(ModelValidationException::STATUS_CODE, 'Указан неправильный client_account_id', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
            }

            $priceLevel = $clientAccount->price_level;
        } else {
            $clientAccount = null;
            $priceLevel = ClientAccount::DEFAULT_PRICE_LEVEL;
        }

        $responseNumbers = [];

        foreach ($numbers->result($limit) as $freeNumber) {
            $responseNumber = $numbers->formattedNumber($freeNumber, $currency, $clientAccount);

            $tariffStatusId = $freeNumber->didGroup->{'tariff_status_main' . $priceLevel};
            $packageStatusIds = [
                $freeNumber->didGroup->{'tariff_status_main' . $priceLevel},
                $freeNumber->didGroup->tariff_status_beauty,
            ];
            $responseNumber->default_tariff = $this->_getDefaultTariff($clientAccount, $tariffStatusId, $packageStatusIds, $freeNumber->city_id);
            $responseNumbers[] = $responseNumber;
        }

        return [
            'total' => $numbers->count(),
            'numbers' => $responseNumbers,
        ];
    }

    /**
     * @param ClientAccount $clientAccount
     * @param int $tariffStatusId
     * @param int[] $packageStatusIds
     * @param int $voipCityId
     * @return array
     */
    private function _getDefaultTariff($clientAccount, $tariffStatusId, $packageStatusIds, $voipCityId)
    {
        if (!$clientAccount) {
            return [];
        }

        $tariffStatusIdKey = $tariffStatusId . '_' . implode('_', $packageStatusIds);
        if (isset($this->_defaultTariffCache[$tariffStatusIdKey])) {
            // взять из кэша
            return $this->_defaultTariffCache[$tariffStatusIdKey];
        }

        $isDefault = true;
        $serviceTypeId = ServiceType::ID_VOIP;
        $countryId = $clientAccount->country_id;
        $currencyId = $clientAccount->country->currency_id;
        $isPostpaid = $clientAccount->is_postpaid;
        $tariffPersonId = ($clientAccount->contragent->legal_type == ClientContragent::PERSON_TYPE) ?
            TariffPerson::ID_NATURAL_PERSON :
            TariffPerson::ID_LEGAL_PERSON;

        $tariffQuery = Tariff::find();
        $tariffTableName = Tariff::tableName();
        $serviceTypeId && $tariffQuery->andWhere([$tariffTableName . '.service_type_id' => (int)$serviceTypeId]);
        $countryId && $tariffQuery->andWhere([$tariffTableName . '.country_id' => (int)$countryId]);
        $currencyId && $tariffQuery->andWhere([$tariffTableName . '.currency_id' => $currencyId]);
        !is_null($isDefault) && $tariffQuery->andWhere([$tariffTableName . '.is_default' => (int)$isDefault]);
        !is_null($isPostpaid) && $tariffQuery->andWhere([$tariffTableName . '.is_postpaid' => (int)$isPostpaid]);
        $tariffStatusId && $tariffQuery->andWhere([$tariffTableName . '.tariff_status_id' => (int)$tariffStatusId]);
        $tariffPersonId && $tariffQuery->andWhere([$tariffTableName . '.tariff_person_id' => [TariffPerson::ID_ALL, $tariffPersonId]]);

        if ($voipCityId) {
            $tariffQuery->joinWith('voipCities');
            $tariffVoipCityTableName = TariffVoipCity::tableName();
            $tariffQuery->andWhere([$tariffVoipCityTableName . '.city_id' => $voipCityId]);
        }

        /** @var Tariff $tariff */
        $tariff = $tariffQuery->one();
        if (!$tariff) {
            return $this->_defaultTariffCache[$tariffStatusIdKey] = [];
        }

        $tariffPeriods = $tariff->tariffPeriods;
        $tariffPeriod = reset($tariffPeriods);

        /** @var TariffResource $tariffResources */
        $tariffResources = $tariff->getTariffResource(Resource::ID_VOIP_LINE)->one();

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
            ->joinWith('voipCities')
            ->where([
                $tariffTableName . '.service_type_id' => ServiceType::ID_VOIP_PACKAGE,
                $tariffTableName . '.country_id' => $tariff->country_id,
                $tariffTableName . '.currency_id' => $tariff->currency_id,
                $tariffTableName . '.is_default' => 1,
                $tariffTableName . '.is_postpaid' => 0,
                $tariffTableName . '.tariff_status_id' => $packageStatusIds,
                $tariffTableName . '.tariff_person_id' => [TariffPerson::ID_ALL, TariffPerson::ID_NATURAL_PERSON],
                TariffVoipCity::tableName() . '.city_id' => array_keys($tariff->voipCities),
            ]);
        /** @var Tariff $tariffPackage */
        foreach ($tariffPackagesQuery->each() as $tariffPackage) {

            // стоимость звонков
            $packagePrices = $tariffPackage ? $tariffPackage->packagePrices : null;
            if (count($packagePrices)) {
                $defaultTariff['call_price_mobile'] = array_shift($packagePrices)->price;
            }

            if (count($packagePrices)) {
                $defaultTariff['call_price_local'] = array_shift($packagePrices)->price;
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
     * @SWG\Definition(definition = "freeNumberNdcRecord", type = "object",
     *   @SWG\Property(property = "ndc", type = "integer", description = "NDC"),
     *   @SWG\Property(property = "numbers", type = "array", description = "Номера", @SWG\Items(ref = "#/definitions/freeNumberRecord"))
     * ),
     *
     * @SWG\Get(tags = {"Numbers"}, path = "/open/get-free-numbers-by-ndc", summary = "Выбрать список свободных номеров и сгруппировать по NDC", operationId = "getFreeNumbersByNdc",
     *   @SWG\Parameter(name = "regions[0]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "regions[1]", type = "integer", description = "Код региона(ов)", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndcType", type = "integer", description = "Тип номеров", in = "query", default = ""),
     *   @SWG\Parameter(name = "minCost", type = "number", description = "Минимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "maxCost", type = "number", description = "Максимальная цена", in = "query", default = ""),
     *   @SWG\Parameter(name = "beautyLvl", type = "integer", description = "Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)", in = "query", default = ""),
     *   @SWG\Parameter(name = "like", type = "string", description = "Маска номера телефона. Синтахис: '.' - один символ, '*' - любое кол-во символов", in = "query", default = ""),
     *   @SWG\Parameter(name = "mask", type = "string", description = "Маска номера телефона. Допустимы [A-Z0-9*]", in = "query", default = ""),
     *   @SWG\Parameter(name = "offset", type = "integer", description = "Смещение результатов поиска", in = "query", default = ""),
     *   @SWG\Parameter(name = "limit", type = "integer", description = "Кол-во записей (default: 12, 'null' для получения всех)", in = "query", default = ""),
     *   @SWG\Parameter(name = "currency", type = "string", description = "Код валюты (ISO)", in = "query", default = ""),
     *   @SWG\Parameter(name = "countryCode", type = "integer", description = "Код страны", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[0]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "cities[1]", type = "integer", description = "ID города", in = "query", default = ""),
     *   @SWG\Parameter(name = "similar", type = "string", description = "Значение для подсчета схожести", in = "query", default = ""),
     *   @SWG\Parameter(name = "ndc", type = "integer", description = "NDC", in = "query", default = ""),
     *   @SWG\Response(response = 200, description = "Выбрать список свободных номеров  и сгруппировать по NDC", @SWG\Items(ref = "#/definitions/freeNumberNdcRecord")),
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
     * @return array
     */
    public function actionGetFreeNumbersByNdc(
        array $regions = [],
        $ndcType = null,
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $mask = null,
        $offset = 0,
        $limit = FreeNumberFilter::FREE_NUMBERS_LIMIT,
        $currency = Currency::RUB,
        $countryCode = 0,
        array $cities = [],
        $similar = null,
        $ndc = null
    ) {
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
            ->orderBy(['number' => SORT_ASC]);

        if ((int)$offset) {
            $numbers->setOffset((int)$offset);
        }

        if ((int)$ndcType) {
            $numbers->setNdcType((int)$ndcType);
        }

        $response = [];

        $distinctNdcs = $numbers->getDistinct('ndc');
        foreach ($distinctNdcs as $distinctNdc) {

            $numbersCloned = clone $numbers;
            $numbersCloned->setNdc($distinctNdc);

            $responseTmp = [];

            foreach ($numbersCloned->result($limit) as $freeNumberFilter) {
                $responseTmp[] = $numbersCloned->formattedNumber($freeNumberFilter, $currency);
            }

            $response[] = [
                'ndc' => (int)$distinctNdc,
                'numbers' => $responseTmp,
            ];
        }

        return $response;
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
     * @SWG\Get(tags = {"Numbers"}, path = "/open/did-groups", summary = "Получение DID групп", operationId = "didGroups",
     *   @SWG\Parameter(name = "id[0]", type = "integer", description = "идентификатор(ы) DID групп", in = "query", default = "",),
     *   @SWG\Parameter(name = "id[1]", type = "integer", description = "идентификатор(ы) DID групп", in = "query", default = ""),
     *   @SWG\Parameter(name="beautyLvl[0]",type="integer",description="Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)",in="query"),
     *   @SWG\Parameter(name="beautyLvl[1]",type="integer",description="Красивость (0 - Стандартный, 1 - Платиновый, 2 - Золотой, 3 - Серебряный, 4 - Бронзовый)",in="query"),
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