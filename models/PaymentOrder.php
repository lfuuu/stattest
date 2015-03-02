<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int    $client_id
 * @property string $bill_no
 * @property int    $payment_id
 * @property float  $sum
 * @property
 */
class PaymentOrder extends ActiveRecord
{
    public static function tableName()
    {
        return 'newpayments_orders';
    }
}