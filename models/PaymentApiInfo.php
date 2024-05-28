<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\payments\makeInfo\PaymentMakeInfoFactory;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $payment_id             идентификатор платежа
 * @property int $created_at
 * @property int $info_json
 * @property int $request
 * @property int $channel
 * @property string $payment_no
 * @property string $operation_id
 * @property string $log
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

    public function getInfoJsonAsJsin()
    {
        return json_decode($this->info_json ?? '{}', true);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        PaymentMakeInfoFactory::me()
            ->getInformatorByApiAnfo($this)
            ->savePaymentInfo()
            ->saveShortInfo();
    }
}
