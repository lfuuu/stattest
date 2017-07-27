<?php

namespace app\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;

class PayPalPayment extends ActiveRecord
{
    public static function tableName()
    {
        return 'paypal_payment';
    }

    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::className(),
        ];
    }
}
