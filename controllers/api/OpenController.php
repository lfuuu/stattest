<?php

namespace app\controllers\api;

use Yii;
use yii\web\Controller;
use app\exceptions\web\BadRequestHttpException;
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
     *   tags={"Cписок свободных номеров"},
     *   path="/open/get-free-numbers",
     *   summary="Выбрать список свободных номеров по одному региону",
     *   operationId="Выбрать список свободных номеров по одному региону",
     *   @SWG\Parameter(name="region",type="integer",description="код региона",in="formData"),
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
                ->getNumbers()
                ->setRegions([$region]);

        $response = [];
        foreach($numbers->each()->result(null) as $r) {
            $response[] = [
                'number' => $r->number,
                'beauty' => $r->beauty_level,
                'price' => $r->price,
                'region' => $r->region,
            ];
        }
        return $response;
    }

    /**
     * @SWG\Get(
     *   tags={"Cписок свободных номеров"},
     *   path="/open/get-free-numbers-by-filter",
     *   summary="Выбрать список свободных номеров в зависимости от параметров",
     *   operationId="Выбрать список свободных номеров в зависимости от параметров",
     *   @SWG\Parameter(name="regions[]",type="integer[]",description="код региона(ов)",in="formData"),
     *   @SWG\Parameter(name="regions[]",type="integer[]",description="код региона(ов)",in="formData"),
     *   @SWG\Parameter(name="minCost",type="float",description="минимальная стоимость",in="formData"),
     *   @SWG\Parameter(name="maxCost",type="float",description="максимальная стоимость",in="formData"),
     *   @SWG\Parameter(name="beautyLvl",type="integer",description="уровень красоты",in="formData"),
     *   @SWG\Parameter(name="like",type="string",description="выражение для поиска вхождения",in="formData"),
     *   @SWG\Parameter(name="offset",type="integer",description="смещение результатов поиска",in="formData"),
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
        if (!is_null($like)) {
            if (!preg_match('#^%?\d{3,}%$#', $like)) {
                throw new BadRequestHttpException('Bad format for mask search');
            }
            $numbers->numberMask = $like;
        }
        if ((int) $beautyLvl) {
            $numbers->beautyLvl = $beautyLvl;
        }
        if ((int) $offset) {
            $numbers->offset = $offset;
        }

        $response = [];

        foreach($numbers->orderByPrice()->each()->result() as $row) {
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
