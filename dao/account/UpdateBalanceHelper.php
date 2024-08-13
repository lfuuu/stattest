<?php

namespace app\dao\account;

use app\exceptions\ModelValidationException;
use app\models\Bill;
use app\models\GoodsIncomeOrder;
use app\models\Invoice;
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
                    $billSum = (float)$bill['sum'];
                } else {
                    break;
                }
            }

            if ($isNextPay) {
                $pay = current($pays);
                if ($pay) {
                    next($pays);
                    $paySum = (float)$pay['sum'];
                } else {
                    break;
                }
            }

            if (!$bill && $pay) { // платежи кидаем на последний счет

                $alreadPayIdx = null;
                array_filter($bills[$billIdx]['p'], function ($p, $k) use (&$alreadPayIdx, $pay) {
                    $alreadPayIdx = $k;
                    return $p['id'] == $pay['id'];
                }, ARRAY_FILTER_USE_BOTH);

                if (is_null($alreadPayIdx)) {
                    $bills[$billIdx]['p'][] = $pay + ['sum_t' => round($paySum, 2)];
                } else {
                    $bills[$billIdx]['p'][$alreadPayIdx]['sum_t'] += $paySum;
                }

                $isNextPay = true;
            } elseif (abs(abs($billSum) - abs($paySum)) < 0.01) {
                $bills[$billIdx]['new_is_payed'] = 1;
                $bills[$billIdx]['p'][] = $pay + ['sum_t' => round($paySum, 2)];
                $isNextBill = $isNextPay = true;
            } elseif (abs($billSum) > abs($paySum)) {
                $bills[$billIdx]['new_is_payed'] = 2;
                $bills[$billIdx]['p'][] = $pay + ['sum_t' => round($paySum, 2)];
                $billSum -= $paySum;
                $isNextBill = false;
                $isNextPay = true;
            } elseif (abs($billSum) < abs($paySum)) {
                $bills[$billIdx]['new_is_payed'] = 1;
                $bills[$billIdx]['payment_date'] = $pay['oper_date'];
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

        return true;
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

        return true;
    }

    public static function saveInvoicesIfPayed($invoices)
    {
        foreach ($invoices as $invoice) {
            if ($invoice['is_payed'] == $invoice['new_is_payed']) {
                continue;
            }

            /** @var Invoice $invoiceModel */
            $invoiceModel = Invoice::find()->where(['id' => $invoice['id']])->one();
            $invoiceModel->is_payed = $invoice['new_is_payed'];
            $invoiceModel->payment_date = $invoice['payment_date'];

            if (!$invoiceModel->save()) {
                throw new ModelValidationException($invoiceModel);
            }
        }

        return true;
    }

    public static function saveInvoicesRejected($invoices)
    {
        $invoiceModels = Invoice::find()
            ->where(['id' => array_map(fn($invoice) => $invoice['id'], $invoices)])
            ->andWhere(['not', ['is_payed' => -1]])
            ->all();

        /** @var Invoice $invoice */
        foreach($invoiceModels as $invoice) {
            $invoice->is_payed = -1;

            if (!$invoice->save()) {
                throw new ModelValidationException($invoice);
            }
        }

        return true;
    }
}