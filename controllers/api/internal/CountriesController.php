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
     *   summary="Получение списка город в стране",
     *   operationId="Получение списка город в стране",
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
        $requestDate = $this->requestParams;
        $countryId = isset($requestDate['country_id']) ? $requestDate['country_id'] : null;
        $withNumbers = isset($requestDate['with_numbers']) ? (int) $requestDate['with_numbers'] : 0;

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

}