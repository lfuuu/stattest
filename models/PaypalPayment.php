<?php
namespace app\models;

use yii\db\ActiveRecord;

class PayPalPayment extends ActiveRecord
{
    public static function tableName()
    {
        return 'paypal_payment';
    }
}
