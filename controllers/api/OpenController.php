<?php

namespace app\controllers\api;

use Yii;
use yii\web\Controller;
use app\models\Number;

final class OpenController extends Controller
{

    public $enableCsrfValidation = false;

    public function init()
    {
	Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /**
     * @SWG\Get(
     *   tags={"Выбрать список свободных номеров по одному региону"},
     *   path="/api/open/",
     *   summary="Выбрать список свободных номеров по одному региону",
     *   operationId="Выбрать список свободных номеров по одному региону",
     *   @SWG\Parameter(name="regions",type="integer[]",description="код региона",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="Выбрать список свободных номеров",
     *     @SWG\Definition(
     *       type="object[]"
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
    public function actionGetFreeNumbers($region = null)
    {
        $numbers = Number::dao()->getFreeNumbersByRegion($region);

        $response = [];
        foreach($numbers as $r) {
            $response []= [ "number" => $r->number, "beauty" => $r->beauty_level, "price" => $r->price, "region" => $r->region ];
        }
        return $response;
    }

    /**
     * @SWG\Get(
     *   tags={"Выбрать список свободных номеров по списку регинов"},
     *   path="/api/open/",
     *   summary="Выбрать список свободных номеров по списку регионов",
     *   operationId="Выбрать список свободных номеров по списку регинов",
     *   @SWG\Parameter(name="regions",type="integer[]",description="код региона",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="Выбрать список свободных номеров",
     *     @SWG\Definition(
     *       type="object[]"
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
    public function actionGetFreeNumbersByList(array $regions = [])
    {
        $response = [];
	foreach ($regions as $region) {
            $numbers = Number::dao()->getFreeNumbersByRegion($region);

            foreach($numbers as $r) {
                $response []= [
		    "number" => $r->number,
		    "beauty" => $r->beauty_level,
		    "price" => $r->price,
		    "region" => $r->region
		];
            }
	}
        return $response;
    }


}
