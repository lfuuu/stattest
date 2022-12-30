<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\helpers\ArrayHelper;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use app\models\City;
use app\models\ClientAccount;
use app\models\Country;
use app\models\dictionary\PublicSite;
use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\models\Region;
use app\modules\uu\models\AccountTariff;
use InvalidArgumentException;
use yii\db\Expression;

class CountriesController extends ApiInternalController
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
     * @SWG\Definition(definition = "cityRecord", type = "object",
     *   @SWG\Property(property = "city_id", type = "integer", description = "Идентификатор города"),
     *   @SWG\Property(property = "city_name", type = "string", description = "Название города"),
     *   @SWG\Property(property = "city_name_translit", type = "string", description = "Название города. Транслит."),
     *   @SWG\Property(property = "region", type = "object", description = "Регион (точка подключения)", ref = "#/definitions/idNameRecord"),
     *   @SWG\Property(property = "free_numbers_count", type = "integer", description = "Кол-во доступных для покупки номерных ёмкостей"),
     *   @SWG\Property(property = "weight", type = "integer", description = "Вес"),
     *   @SWG\Property(property = "ndcs", type = "array", description = "NDC", @SWG\Items(type = "integer")),
     *   @SWG\Property(property = "ndc_type_ids", type = "array", description = "Типы NDC", @SWG\Items(type = "integer"))
     * ),
     *
     * @SWG\Post(tags = {"Dictionaries"}, path = "/internal/countries/get-cities/", summary = "Получение списка городов в стране", operationId = "Получение списка городов в стране",
     *   @SWG\Parameter(name = "country_id", type = "integer", description = "Идентификатор страны", in = "formData", required  =  true, default = ""),
     *   @SWG\Parameter(name = "with_numbers", type = "integer", description = "Признак возврата кол-ва свободных номеров: 0/1", in = "formData", default = "0"),
     *   @SWG\Parameter(name = "with_ndcs", type = "integer", description = "Признак возврата NDC: 0/1", in = "formData", default = "0"),
     *   @SWG\Parameter(name = "with_ndc_type_ids", type = "integer", description = "Признак возврата типов NDC: 0/1", in = "formData", default = "0"),
     *
     *   @SWG\Response(response = 200, description = "список городов в запрашиваемой стране", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/cityRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetCities()
    {
        $requestData = $this->requestParams;
        $countryId = isset($requestData['country_id']) ? $requestData['country_id'] : null;
        $withNumbers = isset($requestData['with_numbers']) ? (int)$requestData['with_numbers'] : 0;
        $withNdcs = isset($requestData['with_ndcs']) ? (int)$requestData['with_ndcs'] : 0;
        $withNdcTypeIds = isset($requestData['with_ndc_type_ids']) ? (int)$requestData['with_ndc_type_ids'] : 0;

        return $this->_getCities($countryId, $withNumbers, $withNdcs, $withNdcTypeIds, false);
    }

    /**
     * @SWG\Post(tags = {"Dictionaries"}, path = "/internal/countries/get-cities__for-api-mcn-ru/", summary = "Получение списка городов в стране", operationId = "Получение списка городов в стране (для api.mcn.ru)",
     *   @SWG\Parameter(name = "country_id", type = "integer", description = "Идентификатор страны", in = "formData", required  =  true, default = ""),
     *   @SWG\Parameter(name = "with_numbers", type = "integer", description = "Признак возврата кол-ва свободных номеров: 0/1", in = "formData", default = "0"),
     *   @SWG\Parameter(name = "with_ndcs", type = "integer", description = "Признак возврата NDC: 0/1", in = "formData", default = "0"),
     *   @SWG\Parameter(name = "with_ndc_type_ids", type = "integer", description = "Признак возврата типов NDC: 0/1", in = "formData", default = "0"),
     *
     *   @SWG\Response(response = 200, description = "список городов в запрашиваемой стране", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/cityRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @return array
     * @throws BadRequestHttpException
     */

    public function actionGetCities__forApiMcnRu()
    {
        $requestData = $this->requestParams;
        $countryId = isset($requestData['country_id']) ? $requestData['country_id'] : null;
        $withNumbers = isset($requestData['with_numbers']) ? (int)$requestData['with_numbers'] : 0;
        $withNdcs = isset($requestData['with_ndcs']) ? (int)$requestData['with_ndcs'] : 0;
        $withNdcTypeIds = isset($requestData['with_ndc_type_ids']) ? (int)$requestData['with_ndc_type_ids'] : 0;

        return $this->_getCities($countryId, $withNumbers, $withNdcs, $withNdcTypeIds, true);
    }


    private function _getCities($countryId, $withNumbers, $withNdcs, $withNdcTypeIds, $isApi)
    {

        if (!$countryId || !($country = Country::findOne($countryId))) {
            throw new BadRequestHttpException;
        }

        $showLevelWhere = ['is_show_in_lk' => City::IS_SHOW_IN_LK_FULL];
        $isApi && $showLevelWhere = ['>=', 'is_show_in_lk', City::IS_SHOW_IN_LK_API_ONLY];

        $result = [];
        /** @var City[] $cities */
        $cities = City::find()
            ->where([
                'in_use' => 1,
            ])
            ->andWhere($showLevelWhere)
            ->andWhere($countryId ? ['country_id' => $countryId] : [])
            ->orderBy([
                'order' => SORT_ASC,
                'name' => SORT_ASC,
            ])
            ->indexBy('id')
            ->all();

        $citieIds = array_keys($cities);

        $freeNumbersCountData = $ndcsData = $ndcTypeIdsData = [];

        if ($withNumbers || $withNdcs) {
            $query = (new FreeNumberFilter)
                ->setIsService(false)
                ->setCities($citieIds)
                ->getQuery()
                ->groupBy('city_id')
                ->indexBy('city_id')
                ->asArray();
        }

        if ($withNumbers) {
            $freeNumbersCountQuery = clone $query;
            $freeNumbersCountData = $freeNumbersCountQuery->select([
                'count' => new Expression('COUNT(*)')
            ])
                ->column();
        }

        if ($withNdcs) {
            $ndcsQuery = clone $query;
            $ndcsData = $ndcsQuery->select([
                'nds' => new Expression('GROUP_CONCAT(DISTINCT ndc)')
            ])
                ->column();
        }

        if ($withNdcTypeIds) {
            $ndcTypeIdsPreData = (new FreeNumberFilter)
                ->setIsService(false)
                ->setCities($citieIds)
                ->getQuery()
                ->distinct()
                ->select(['city_id', 'ndc', 'ndc_type_id'])
                ->asArray()
                ->all();

            foreach($ndcTypeIdsPreData as $d) {
                $ndcTypeIdsData[$d['city_id']][$d['ndc']] = $d['ndc_type_id'];
            }
        }

        foreach ($cities as $city) {

            $freeNumbersCount = $withNumbers && isset($freeNumbersCountData[$city->id]) ? $freeNumbersCountData[$city->id] : 0;

            $ndcs = $withNdcs && isset($ndcsData[$city->id]) ? explode(",", $ndcsData[$city->id]) : [];

            $ndcTypeIds = $withNdcTypeIds && isset($ndcTypeIdsData[$city->id]) ? $ndcTypeIdsData[$city->id]: [];

            $result[] = [
                'city_id' => $city->id,
                'city_name' => $city->name,
                'city_name_translit' => $city->name_translit,
                'region' => $this->_getIdNameRecord($city->region),
                'free_numbers_count' => (int)$freeNumbersCount,
                'weight' => $city->order,
                'ndcs' => $ndcs,
                'ndc_type_ids' => $ndcTypeIds,
            ];
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition = "countryRecord", type = "object",
     *   @SWG\Property(property = "country_code", type = "integer", description = "Идентификатор страны"),
     *   @SWG\Property(property = "country_title", type = "string", description = "Название страны по-английски"),
     *   @SWG\Property(property = "country_rus", type = "string", description = "Название страны по-русски сокращенно"),
     *   @SWG\Property(property = "country_rus_full", type = "string", description = "Название страны по-русски полностью"),
     *   @SWG\Property(property = "country_lang", type = "string", description = "Используемый язык"),
     *   @SWG\Property(property = "country_currency", type = "string", description = "Используемая валюта"),
     *   @SWG\Property(property = "country_weight", type = "string", description = "Порядок вывода"),
     *   @SWG\Property(property = "country_prefix", type = "string", description = "Телефонный префикс"),
     *   @SWG\Property(property = "country_alpha_3", type = "string", description = "3-буквенный код страницы"),
     *   @SWG\Property(property = "country_site", type = "string", description = "URL сайта"),
     *   @SWG\Property(property = "regions", type = "array", @SWG\Items(type = "integer"))
     * ),
     *
     * @SWG\Post(tags = {"Dictionaries"}, path = "/internal/countries/get-countries-by-domain", summary = "Получение списка стран по домену", operationId = "Получение списка стран по домену",
     *   @SWG\Parameter(name = "domain", type = "string", description = "доменное имя", in = "formData", default = ""),
     *
     *   @SWG\Response(response = 200, description = "Список стран для сайта", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/countryRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetCountriesByDomain()
    {
        $requestData = $this->requestParams;
        $domain = isset($requestData['domain']) ? $requestData['domain'] : null;

        if (!$domain) {
            throw new BadRequestHttpException;
        }

        /** @var PublicSite $publicSite */
        $publicSite = PublicSite::find()
            ->where(['domain' => $domain])
            ->with(['publicSiteCountries.country', 'publicSiteCountries.publicSiteNdcTypes', 'publicSiteCountries.country'])
            ->one();
        if (!$publicSite) {
            throw new BadRequestHttpException;
        }

        $result = [];
        foreach ($publicSite->publicSiteCountries as $publicSiteCountry) {
            $result[] = $this->_countryInfo($publicSiteCountry->country) + ['ndc_type_ids' => ArrayHelper::getColumn($publicSiteCountry->publicSiteNdcTypes, 'ndc_type_id')];
        }

        return $result;
    }

    /**
     * @SWG\Get(tags = {"Dictionaries"}, path = "/internal/countries/get-countries", summary = "Получение списка стран", operationId = "Получение списка стран",
     *   @SWG\Parameter(name = "is_with_site_only", type = "integer", description = "Только имеющие сайт", in = "query", default = "0"),
     *
     *   @SWG\Response(response = 200, description = "Список стран", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/countryRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param int $is_with_site_only
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetCountries($is_with_site_only = null)
    {
        $countries = Country::find()
            ->where([
                'in_use' => 1,
                'is_show_in_lk' => 1,
            ])
            ->orderBy(['order' => SORT_ASC]);

        $is_with_site_only && $countries->andWhere(['>', 'site', '']);

        $result = [];

        foreach ($countries->each() as $country) {
            /** @var Country $country */
            $result[] = $this->_countryInfo($country);
        }

        return $result;
    }

    /**
     * @param Country $country
     * @return array
     */
    private function _countryInfo(Country $country)
    {
        $regions = $country->getRegions()->select('id')->column();

        return [
            'country_code' => $country->code,
            'country_title' => $country->name,
            'country_rus' => $country->name_rus,
            'country_rus_full' => $country->name_rus_full,
            'country_lang' => $country->lang,
            'country_currency' => $country->currency_id,
            'country_weight' => $country->order,
            'country_prefix' => $country->prefix,
            'country_alpha_3' => $country->alpha_3,
            'country_site' => $country->site,
            'regions' => $regions,
        ];
    }

    /**
     * @SWG\Definition(definition = "regionRecord", type = "object",
     *   @SWG\Property(property = "id", type = "integer", description = "Идентификатор"),
     *   @SWG\Property(property = "key", type = "string", description = "Ключ для перевода"),
     *   @SWG\Property(property = "name", type = "string", description = "Название"),
     *   @SWG\Property(property = "short_name", type = "string", description = "Короткое название"),
     *   @SWG\Property(property = "code", type = "integer", description = "Код"),
     *   @SWG\Property(property = "timezone_name", type = "string", description = "Таймзона"),
     *   @SWG\Property(property = "country", type = "object", description = "Страна", ref  =  "#/definitions/idNameRecord")),
     * ),
     *
     * @SWG\Get(tags = {"Dictionaries"}, path = "/internal/countries/get-regions", summary = "Получение списка регионов (точек подключения)", operationId = "Получение списка регионов (точек подключения)",
     *   @SWG\Parameter(name  =  "country_id", type  =  "integer", description  =  "ID страны", in  =  "query", default  =  ""),
     *   @SWG\Parameter(name  =  "client_account_id", type  =  "integer", description  =  "ID ЛС (для определения по нему страны)", in  =  "query", default  =  ""),
     *
     *   @SWG\Response(response = 200, description = "Список регионов", @SWG\Schema(type = "array", @SWG\Items(ref = "#/definitions/regionRecord"))),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     *
     * @param int $country_id
     * @param int $client_account_id
     * @return array
     * @throws \InvalidArgumentException
     */
    public function actionGetRegions(
        $country_id = null,
        $client_account_id = null
    )
    {
        $regions = Region::find()
            ->where([
                'is_active' => 1,
                'type_id' => Region::TYPE_NODE,
            ])
            ->orderBy([
                new Expression('id > 97 DESC'),
                'name' => SORT_ASC,
            ]);

        if ($client_account_id) {
            // взять страну от ЛС
            $clientAccount = ClientAccount::findOne(['id' => $client_account_id]);
            if (!$clientAccount) {
                throw new InvalidArgumentException('Указан неправильный client_account_id', AccountTariff::ERROR_CODE_ACCOUNT_EMPTY);
            }

            $country_id = $clientAccount->country_id;
        }

        if ($country_id) {
            if ($country_id == Country::RUSSIA) {
                $regions->andWhere(['country_id' => (int)$country_id]);
            } else {
                $regions->andWhere(['id' => Region::ID_NON_RUSSIA]); // @todo переделать без костылей
            }
        }

        $result = [];

        /** @var Region $region */
        foreach ($regions->each() as $region) {
            /** @var Country $country */
            $result[] = [
                'id' => $region->id,
                'key' => 'operator_region_' . $region->id,
                'name' => $region->name,
                'short_name' => $region->short_name,
                'code' => $region->code,
                'timezone_name' => $region->timezone_name,
                'country' => $this->_getIdNameRecord($region->country, 'code'),
            ];
        }

        return $result;
    }

}