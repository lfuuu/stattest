<?php

namespace app\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;

/**
 * Class PayPalPayment
 *
 * @property string token
 * @property string created_at
 * @property integer client_id
 * @property string currency
 * @property float sum
 * @property string payer_id
 * @property string payment_id
 * @property string data1
 * @property string data2
 * @property string data3
 */
class PayPalPayment extends ActiveRecord
{
    public static function tableName()
    {
        return 'paypal_payment';
    }

    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::class,
        ];
    }
}
