<?php

namespace app\controllers\api;

use Yii;
use app\classes\BaseController;
use app\models\Number;

final class OpenController extends BaseController
{

    public function init()
    {
	Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    public function actionGetFreeNumbers($region = null)
    {
        $numbers = Number::dao()->getFreeNumbersByRegion($region);

        $response = [];
        foreach($numbers as $r) {
            $response []= [ "number" => $r->number, "beauty" => $r->beauty_level, "price" => $r->price, "region" => $r->region ];
        }
        return $response;
    }

    public function actionGetFreeNumbersByList(array $regions = [])
    {
        $response = [];
	foreach ($regions as $region) {
            $numbers = Number::dao()->getFreeNumbersByRegion($region);

            foreach($numbers as $r) {
                $response []= [ "number" => $r->number, "beauty" => $r->beauty_level, "price" => $r->price, "region" => $r->region];
            }
	}
        return $response;
    }


}
