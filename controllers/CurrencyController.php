<?php
namespace app\controllers;

use app\classes\Assert;
use app\forms\buh\PaymentAddForm;
use app\models\ClientAccount;
use app\models\Currency;
use app\models\CurrencyRate;
use Yii;
use app\classes\BaseController;


class CurrencyController extends BaseController
{
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

    public function actionGetRate($from, $to)
    {
        if ($from == $to) {
            return 1;
        }

        $rate = CurrencyRate::dao()->getRate($from, $to, new \DateTime());

        if (!$rate && $from != 'RUB') {
            $rate = CurrencyRate::dao()->getRate($to, $from, new \DateTime());
            if ($rate) {
                $rate = 1 / $rate;
            }
        }

        if ($rate) {
            $rate = round($rate, 8);
        }

        return $rate;
    }
}