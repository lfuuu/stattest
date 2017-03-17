<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class PaymentOrder
 *
 * @property int $client_id
 * @property string $bill_no
 * @property int $payment_id
 * @property float $sum
 */
class PaymentOrder extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'newpayments_orders';
    }
}