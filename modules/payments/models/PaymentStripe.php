<?php

namespace app\modules\payments\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;

/**
 * Class PaymentStripe
 *
 * @property int $payment_id
 * @property string $token_id
 * @property int $account_id
 * @property string $created_at
 * @property float $sum
 * @property string $currency
 * @property string $token_data
 */
class PaymentStripe extends ActiveRecord
{
    public static function tableName()
    {
        return 'payment_stripe';
    }

    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::className(),
        ];
    }
}
