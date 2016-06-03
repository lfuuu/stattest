<?php

namespace app\controllers\api\internal;

use Yii;
use app\classes\ApiInternalController;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\web\BadRequestHttpException;
use app\models\Country;
use app\models\City;
use app\models\filter\FreeNumberFilter;

class CountriesController extends ApiInternalController
{

    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Definition(
     *   definition="cityRecord",
     *   type="object",
     *   @SWG\Property(
     *     property="city_id",
     *     type="integer",
     *     description="Идентификатор города"
     *   ),
     *   @SWG\Property(
     *     property="city_title",
     *     type="string",
     *     description="Название города"
     *   ),
     *   @SWG\Property(
     *     property="free_numbers_count",
     *     type="boolean",
     *     description="Кол-во доступных для покупки номерных ёмкостей"
     *   )
     * ),
     * @SWG\Post(
     *   tags={"Справочники"},
     *   path="/internal/countries/get-cities/",
     *   summary="Получение списка городов в стране",
     *   operationId="Получение списка городов в стране",
     *   @SWG\Parameter(name="country_id",type="integer",description="идентификатор страны",in="formData"),
     *   @SWG\Parameter(name="with_numbers",type="integer",description="признак возврата кол-ва свободных номеров",in="formData"),
     *
     *   @SWG\Response(
     *     response=200,
     *     description="список городов в запрашиваемой стране",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/cityRecord"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetCities()
    {
        $requestData = $this->requestParams;
        $countryId = isset($requestData['country_id']) ? $requestData['country_id'] : null;
        $withNumbers = isset($requestData['with_numbers']) ? (int) $requestData['with_numbers'] : 0;

        if (!$countryId || !($country = Country::findOne($countryId))) {
            throw new BadRequestHttpException;
        }

        $result = [];
        $cities = City::dao()->getList(false, $countryId);

        foreach ($cities as $cityId => $cityName) {
            $freeNumbersCount =
                $withNumbers
                    ? $freeNumbersCount = (new FreeNumberFilter)->getNumbers()->setCity($cityId)->count()
                    : 0;

            $result[] = [
                'city_id' => $cityId,
                'city_name' => $cityName,
                'free_numbers_count' => $freeNumbersCount,
            ];
        }

        return $result;
    }

    /**
     * @SWG\Definition(
     *   definition="countryRecord",
     *   type="object",
     *   @SWG\Property(
     *     property="country_code",
     *     type="integer",
     *     description="Идентификатор страны"
     *   ),
     *   @SWG\Property(
     *     property="country_title",
     *     type="string",
     *     description="Название страны"
     *   ),
     *   @SWG\Property(
     *     property="country_lang",
     *     type="string",
     *     description="Используемый язык"
     *   ),
     *   @SWG\Property(
     *     property="country_currency",
     *     type="string",
     *     description="Используемая валюта"
     *   )
     * ),
     * @SWG\Post(
     *   tags={"Справочники"},
     *   path="/internal/countries/get-countries-by-domain",
     *   summary="Получение списка стран по домену",
     *   operationId="Получение списка стран по домену",
     *   @SWG\Parameter(name="domain",type="string",description="доменное имя",in="formData"),
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Список стран для сайта",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/countryRecord"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
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

        $countries = Country::find()->where(['site' => $domain])->all();
        $result = [];

        foreach ($countries as $country) {
            /** @var Country $country */
            $result[] = $this->countryInfo($country);
        }

        return $result;
    }

    /**
     * @SWG\Post(
     *   tags={"Справочники"},
     *   path="/internal/countries/get-countries",
     *   summary="Получение списка стран",
     *   operationId="Получение списка стран",
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Список стран",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(
     *         ref="#/definitions/countryRecord"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetCountries()
    {
        $countries = Country::find()->where(['is_use' => 1])->all();
        $result = [];

        foreach ($countries as $country) {
            /** @var Country $country */
            $result[] = $this->countryInfo($country);
        }

        return $result;
    }

    /**
     * @param Country $country
     * @return array[]
     */
    private function countryInfo(Country $country)
    {
        return [
            'country_code' => $country->code,
            'country_title' => $country->name,
            'country_lang' => $country->lang,
            'country_currency' => $country->currency_id,
        ];
    }

}