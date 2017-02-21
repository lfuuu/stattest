<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use app\models\City;
use app\models\Country;
use app\models\dictionary\PublicSite;
use app\models\filter\FreeNumberFilter;
use app\models\Region;
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
     * @SWG\Definition(definition="cityRecord", type="object",
     *   @SWG\Property(property="city_id", type="integer", description="Идентификатор города"),
     *   @SWG\Property(property="city_title", type="string", description="Название города"),
     *   @SWG\Property(property="free_numbers_count", type="integer", description="Кол-во доступных для покупки номерных ёмкостей"),
     *   @SWG\Property(property="ndcs", type="array", description="NDC", @SWG\Items(type="integer"))
     * ),
     *
     * @SWG\Post(tags={"Справочники"}, path="/internal/countries/get-cities/", summary="Получение списка городов в стране", operationId="Получение списка городов в стране",
     *   @SWG\Parameter(name="country_id", type="integer", description="Идентификатор страны", in="formData", required = true, default=""),
     *   @SWG\Parameter(name="with_numbers", type="integer", description="Признак возврата кол-ва свободных номеров: 0/1", in="formData", default="0"),
     *   @SWG\Parameter(name="with_ndcs", type="integer", description="Признак возврата NDC: 0/1", in="formData", default="0"),
     *
     *   @SWG\Response(response=200, description="список городов в запрашиваемой стране", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/cityRecord"))),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
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

        if (!$countryId || !($country = Country::findOne($countryId))) {
            throw new BadRequestHttpException;
        }

        $result = [];
        $cities = City::dao()->getList(false, $countryId);

        foreach ($cities as $cityId => $city) {

            $freeNumbersCount = $withNumbers ?
                (new FreeNumberFilter)->getNumbers()->setCity($cityId)->count() :
                0;

            $ndcs = $withNdcs ?
                (new FreeNumberFilter)->getNumbers()->setCity($cityId)->getDistinctNdc() :
                [];

            $result[] = [
                'city_id' => $cityId,
                'city_name' => (string)$city,
                'free_numbers_count' => (int)$freeNumbersCount,
                'weight' => $city->order,
                'ndcs' => $ndcs,
            ];
        }

        return $result;
    }

    /**
     * @SWG\Definition(definition="countryRecord", type="object",
     *   @SWG\Property(property="country_code", type="integer", description="Идентификатор страны"),
     *   @SWG\Property(property="country_title", type="string", description="Название страны"),
     *   @SWG\Property(property="country_lang", type="string", description="Используемый язык"),
     *   @SWG\Property(property="country_currency", type="string", description="Используемая валюта"),
     *   @SWG\Property(property="regions", type="array", @SWG\Items(type="integer"))
     * ),
     *
     * @SWG\Post(tags={"Справочники"}, path="/internal/countries/get-countries-by-domain", summary="Получение списка стран по домену", operationId="Получение списка стран по домену",
     *   @SWG\Parameter(name="domain", type="string", description="доменное имя", in="formData", default=""),
     *
     *   @SWG\Response(response=200, description="Список стран для сайта", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/countryRecord"))),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
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

        $publicSite = PublicSite::findOne(['domain' => $domain]);
        if (!$publicSite) {
            throw new BadRequestHttpException;
        }

        $result = [];
        foreach ($publicSite->publicSiteCountries as $publicSiteCountry) {
            $result[] = $this->_countryInfo($publicSiteCountry->country);
        }

        return $result;
    }

    /**
     * @SWG\Post(tags={"Справочники"}, path="/internal/countries/get-countries", summary="Получение списка стран", operationId="Получение списка стран",
     *
     *   @SWG\Response(response=200, description="Список стран", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/countryRecord"))),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetCountries()
    {
        $countries = Country::find()
            ->where(['in_use' => 1])
            ->orderBy(['order' => SORT_ASC]);
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
        $regions = Region::find()
            ->select('id')
            ->where(['country_id' => $country->code])
            ->column();

        return [
            'country_code' => $country->code,
            'country_title' => $country->name,
            'country_lang' => $country->lang,
            'country_currency' => $country->currency_id,
            'country_weight' => $country->order,
            'regions' => $regions,
        ];
    }

    /**
     * @SWG\Definition(definition="regionRecord", type="object",
     *   @SWG\Property(property="id", type="integer", description="Идентификатор"),
     *   @SWG\Property(property="key", type="string", description="Ключ для перевода"),
     *   @SWG\Property(property="name", type="string", description="Название"),
     *   @SWG\Property(property="short_name", type="string", description="Короткое название"),
     *   @SWG\Property(property="code", type="integer", description="Код"),
     *   @SWG\Property(property="timezone_name", type="string", description="Таймзона"),
     *   @SWG\Property(property="country", type="object", description="Страна", ref = "#/definitions/idNameRecord")),
     * ),
     *
     * @SWG\Post(tags={"Справочники"}, path="/internal/countries/get-regions", summary="Получение списка регионов (точек подключения)", operationId="Получение списка регионов (точек подключения)",
     *
     *   @SWG\Response(response=200, description="Список регионов", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/regionRecord"))),
     *   @SWG\Response(response="default", description="Ошибки", @SWG\Schema(ref="#/definitions/error_result"))
     * )
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetRegions()
    {
        $regions = Region::find()
            ->where(['is_active' => 1])
            ->orderBy([
                new Expression('id > 97 DESC'),
                'name' => SORT_ASC,
            ]);
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