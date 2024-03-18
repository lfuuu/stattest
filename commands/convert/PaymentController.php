<?php

namespace app\commands\convert;

use app\classes\payments\PaymentParser;
use app\helpers\DateTimeZoneHelper;
use app\models\Payment;
use yii\console\Controller;

class PaymentController extends Controller
{
    public function actionA($file)
    {
        $this->go($file);
    }

    private function go($file)
    {
        static $c = [];

        if (!isset($c[$file])) {
            [$type, $payAccs, $payments] = PaymentParser::Parse($file);

            $c[$file] = $payments;
        } else {
            $payments = $c[$file];
        }

        foreach ($payments as $pay) {

            $sum = $pay['sum'];
            if (isset($payAccs[$pay['account']])) {
                $sum = -$pay['sum'];
            }

            $payment = Payment::find()->where([
                'payment_no' => $pay['pp'],
                'oper_date' => \DateTime::createFromFormat(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED, $pay['oper_date'] ?: $pay['date_dot'])->format(DateTimeZoneHelper::DATE_FORMAT),
                'sum' => $sum,
            ])->one();

            if (!$payment) {
                $payment = Payment::find()->where([
                    'payment_no' => $pay['pp'],
                    'oper_date' => \DateTime::createFromFormat(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED, $pay['date_dot'])->format(DateTimeZoneHelper::DATE_FORMAT),
                    'sum' => $sum,
                ])->one();
            }

            if ($payment) {
                try {
                    $this->_c($payment, $pay);
                }catch (\Exception $e) {
                    echo PHP_EOL ;
                    print_r($e->getMessage());
                }
            } else {
//                echo ' $payment not found';
            }
        }
    }

    private function _c($payment, $pay)
    {
        $info = \app\models\PaymentInfo::find()->where(['payment_id' => $payment->id])->one();

        if (!$info) {
            $info = new \app\models\PaymentInfo();
            $info->payment_id = $payment->id;

            echo " +";
        }

        $info->payer = $pay['payer'];
        $info->payer_inn = $pay['inn'];
        $info->payer_bik = $pay['bik'];
        $info->payer_bank = $pay['a2'];
        $info->payer_account = $pay['account'];

        $info->getter = $pay['geter'];
        $info->getter_inn = $pay['geter_inn'];
        $info->getter_bik = $pay['geter_bik'];
        $info->getter_bank = $pay['geter_bank'];
        $info->getter_account = $pay['geter_acc'];
        $info->comment = $pay['comment'];

        print_r(var_export($info->getDirtyAttributes(), true));

        if (!$info->save()) {
            throw new ModelValidationException($info);
        }
    }
}
