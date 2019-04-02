<?php
namespace app\controllers;

use app\classes\BaseController;
use app\models\CurrencyRate;
use Yii;

class CurrencyController extends BaseController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['get-rate'],
                'roles' => ['@'],
            ],
        ];
        return $behaviors;
    }

    /**
     * @param string $from
     * @param string $to
     * @return float|null
     * @throws \yii\base\Exception
     */
    public function actionGetRate($from, $to)
    {
        return CurrencyRate::dao()->crossRate($from, $to);
    }
}