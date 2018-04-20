<?php

namespace app\commands\convert;

use app\models\Bill;
use yii\console\Controller;

class BillsController extends Controller
{
    /**
     * Сборка данных для колонки `payment_date` модели Bill
     */
    public function actionRebuildPaymentDateColumn()
    {
        Bill::getDb()
            ->createCommand('
                UPDATE newbills newbills
                  INNER JOIN (
                    SELECT
                     bills.id bills_id,
                     COALESCE(payments.payment_date, payments_orders.payment_date, null) payment_date
                    FROM newbills bills
                      LEFT JOIN (
                        SELECT bill_no, MAX(payment_date) payment_date
                        FROM newpayments
                        WHERE sum > 0
                        GROUP BY bill_no
                      ) payments
                        ON payments.bill_no = bills.bill_no
                      LEFT JOIN (
                        SELECT payments_orders_groupped.bill_no, newpayments.payment_date
                        FROM (
                          SELECT bill_no, MAX(payment_id) payment_id
                          FROM newpayments_orders
                          WHERE sum > 0
                          GROUP BY bill_no
                        ) payments_orders_groupped
                          INNER JOIN newpayments
                            ON payments_orders_groupped.payment_id = newpayments.id
                      ) payments_orders
                       ON payments_orders.bill_no = bills.bill_no
                    WHERE bills.is_payed = 1 AND bills.sum > 0
                  ) temporal ON newbills.id = temporal.bills_id
                SET newbills.payment_date = temporal.payment_date
                WHERE newbills.is_payed = 1 AND newbills.sum > 0;
            ')
            ->execute();
    }

    /**
     * Удаление данных из колонки `payment_date` модели Bill
     */
    public function actionClearPaymentDateColumn()
    {
        Bill::updateAll([
            'payment_date' => null,
        ]);
    }
}