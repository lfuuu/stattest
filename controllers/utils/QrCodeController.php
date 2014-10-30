<?php
namespace app\controllers\utils;

use app\classes\BaseController;

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
        \PHPQRCode\QRcode::png(trim($data), false, 'H', 4, 2);
    }
}
