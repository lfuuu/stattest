<?php
namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\models\Bik;
use yii\helpers\Json;
use yii\web\Response;

class DataController extends BaseController
{

    public function actionRpcFindBank1c($value)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Json::encode(Bik::findOne(['bik' => $value]));
    }

}