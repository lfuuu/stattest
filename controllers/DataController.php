<?php
namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\models\Bik;
use yii\helpers\Json;
use yii\web\Response;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\ClientAccount;

class DataController extends BaseController
{

    public function actionRpcFindBank1c($value)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Json::encode(Bik::findOne(['bik' => $value]));
    }

    public function actionGetUsageTariffs($type, $region)
    {
        //if (!Yii::$app->request->getIsAjax())
        //    return false;

        if (!($clientAccount = $this->getFixClient()) instanceof ClientAccount)
            return false;

        Yii::$app->response->format = Response::FORMAT_JSON;
        switch ($type) {
            case 'voip':
                return Json::encode(UsageVoip::dao()->getTariffsList($clientAccount, $region));
                break;
            case 'virtpbx':
                return Json::encode(UsageVirtpbx::dao()->getTariffsList($clientAccount));
                break;
            default:
                break;
        }
    }

}