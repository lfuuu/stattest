<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property string $payment_id             идентификатор платежа
 * @property string $payer
 * @property string $payer_inn
 * @property string $payer_bik
 * @property string $payer_bank
 * @property string $payer_account
 * @property string $getter
 * @property string $getter_inn
 * @property string $getter_bik
 * @property string $getter_bank
 * @property string $getter_account
 * @property string $comment
 */
class PaymentInfo extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newpayment_info';
    }


    public static function primaryKey()
    {
        return ['payment_id'];
    }
}
