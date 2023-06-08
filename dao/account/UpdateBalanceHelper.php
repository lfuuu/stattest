<?php
namespace app\dao\account;

use app\exceptions\ModelValidationException;
use app\models\Bill;
use app\models\GoodsIncomeOrder;
use app\models\PaymentOrder;

class UpdateBalanceHelper
{

    public static function mergePaymentIntoBills(&$bills, $pays)
    {
        $cnt = 0;
        $billSum = $paySum = 0.0;
        $isNextBill = $isNextPay = true;
        $billIdx = null;
        $bill = $pay = null;
        do {
            if ($isNextBill) {
                $bill = current($bills);
                if ($bill) {
                    $billIdx = key($bills);
                    next($bills);
                    $billSum = round($bill['sum'], 2);

                }
            }

            if ($isNextPay) {
                $pay = current($pays);
                if ($pay) {
                    next($pays);
                    $paySum = round($pay['sum'], 2);
                }
                if (!$pay) {
                    break;
                }
            }

            if (!$bill && $pay) { // платежи кидаем на последний счет
                $bills[$billIdx]['p'][] = $pay + ['sum_t' => round($paySum,2)];
                $isNextPay = true;
            }elseif ($billSum == $paySum) {
                $bills[$billIdx]['new_is_payed'] = 1;
                $bills[$billIdx]['p'][] = $pay + ['sum_t' => round($paySum,2)];
                $isNextBill = $isNextPay = true;
            } elseif (abs($billSum) > abs($paySum)) {
                $bills[$billIdx]['new_is_payed'] = 2;
                $bills[$billIdx]['p'][] = $pay + ['sum_t' => round($paySum,2)];
                $billSum -= $paySum;
                $isNextBill = false;
                $isNextPay = true;
            } elseif (abs($billSum) < abs($paySum)) {
                $bills[$billIdx]['new_is_payed'] = 1;
                $bills[$billIdx]['p'][] = $pay + ['sum_t' => round($billSum, 2)];
                $paySum -= $billSum;
                $isNextBill = true;
                $isNextPay = false;
            }
        } while ($cnt++ < 1000);
    }

    public static function paymentOrders_extractFromBills($bills)
    {
        $paymentsOrders = [];

        foreach ($bills as $bill) {
            if (!isset($bill['p'])) {
                continue;
            }

            foreach ($bill['p'] as $pay) {
                $paymentsOrders[] = [
                    'payment_id' => $pay['id'],
                    'bill_no' => $bill['bill_no'],
                    'sum' => $pay['sum_t'],
                ];
            }
        }

        return $paymentsOrders;
    }

    public static function paymentOrders_save($accountId, $paymentOrders)
    {
        $batchInsertPaymentOrders = array_map(function ($order) use ($accountId) {
            return [$order['payment_id'], $order['bill_no'], $accountId, $order['sum']];
        }, $paymentOrders);

        PaymentOrder::deleteAll(['client_id' => $accountId]);

        if ($batchInsertPaymentOrders) {
            return \Yii::$app->db->createCommand()
                ->batchInsert(
                    PaymentOrder::tableName(),
                    ['payment_id', 'bill_no', 'client_id', 'sum'],
                    $batchInsertPaymentOrders
                )->execute();
        }
    }

    public static function saveBillIfPayed($bills)
    {
        foreach ($bills as $v) {
            $billNo = $v['bill_no'];

            if ($v['bill_no'] == 'saldo') {
                continue;
            }

            if ($v['is_payed'] != $v['new_is_payed']) {
                $documentType = Bill::dao()->getDocumentType($billNo);
                if ($documentType['type'] == 'bill') {
                    /** @var Bill $bill */
                    $bill = Bill::findOne(['bill_no' => $billNo]);
                    if ($bill->is_payed != $v['new_is_payed']) {
                        $bill->is_payed = $v['new_is_payed'];
                        $savedBills[$bill->bill_no] = 1;
                        if (!$bill->save()) {
                            throw new ModelValidationException($bill);
                        }
                    }
                } elseif ($documentType['type'] == 'incomegood') {
                    $order = GoodsIncomeOrder::findOne(['number' => $billNo]);
                    $order->is_payed = $v['new_is_payed'];
                    $order->save();
                }
            }
        }

    }


}