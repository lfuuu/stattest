<?php

namespace app\controllers\api;

use Yii;
use yii\web\Controller;
use app\models\Number;
use app\models\filter\FreeNumberFilter;

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
        $numbers =
            (new FreeNumberFilter)
                ->numbers
                ->setRegions([$region])
                ->result(null);

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
    public function actionGetFreeNumbersByFilter(
        array $regions = [],
        $minCost = null,
        $maxCost = null,
        $beautyLvl = null,
        $like = null,
        $offset = 0
    )
    {
        $numbers  = new FreeNumberFilter;
        $numbers->regions = $regions;
        $numbers->minCost = $minCost;
        $numbers->maxCost = $maxCost;
        $numbers->numberMask = $like;
        if ((int) $beautyLvl) {
            $numbers->beautyLvl = $beautyLvl;
        }
        if ((int) $offset) {
            $numbers->offset = $offset;
        }

        $response = [];

        foreach($numbers->orderByPrice()->result() as $row) {
            $response[] = [
                'number' => $row->number,
                'beauty' => $row->beauty_level,
                'price' => $row->price,
                'region' => $row->region,
            ];
        }

        return $response;
    }


}
