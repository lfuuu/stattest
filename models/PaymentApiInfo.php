<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $payment_id             идентификатор платежа
 * @property int $created_at
 * @property int $info_json
 * @property int $request
 * @property int $channel
 * @property int $payment_no
 */


class PaymentApiInfo extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newpayment_api_info';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('UTC_TIMESTAMP()'),
            ],
        ];
    }

}
