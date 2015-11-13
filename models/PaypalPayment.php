<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\classes\behaviors\CreatedAt;

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
