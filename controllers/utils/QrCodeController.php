<?php
namespace app\controllers\utils;

use Yii;
use yii\web\Response;
use app\classes\BaseController;
use app\classes\QRcode\QRcode;

class QrCodeController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        array_unshift(
            $behaviors['access']['rules'],
            [
                'allow' => true,
                'actions' => ['get'],
            ]
        );
        return $behaviors;
    }

    public function actionGet($data)
    {
        $response = Yii::$app->getResponse();
        $response->headers->set('Content-Type', 'image/gif');
        $response->format = Response::FORMAT_RAW;

        QRcode::gif(trim($data), false, 'H', 4, 2);
        //\PHPQRCode\QRcode::png(trim($data), false, 'H', 4, 2);
    }
}
