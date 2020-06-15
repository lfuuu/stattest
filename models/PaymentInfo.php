<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $payment_id             идентификатор платежа
 * @property int $payer_inn
 * @property int $payer_bik
 * @property int $payer_bank
 * @property int $payer_account
 * @property int $getter_inn
 * @property int $getter_bik
 * @property int $getter_bank
 * @property int $getter_account
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
}
