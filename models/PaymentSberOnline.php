<?php

namespace app\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;
use app\dao\PaymentSberOnlineDao;

/**
 * Class PaymentSberOnline
 *
 * @property int $id
 * @property string $payment_sent_date
 * @property string $payment_received_date
 * @property string $code1
 * @property string $code2
 * @property string $code3
 * @property string $code4
 * @property string $payer
 * @property string $description
 * @property float $sum_paid
 * @property float $sum_received
 * @property float $sum_fee
 * @property string $code5
 * @property string $createdAt
 *
 * @package app\models
 */
class PaymentSberOnline extends ActiveRecord
{
    public static function tableName()
    {
        return 'payment_sber_online';
    }

    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::class,
        ];
    }

    public static function dao()
    {
        return PaymentSberOnlineDao::me();
    }

    /**
     * Сохранен ли платеж в базу или нет.
     * @return bool
     */
    public function isSaved()
    {
        return self::find()->where([
                'payment_sent_date' => $this->payment_sent_date,
                'code1' => $this->code1,
                'code2' => $this->code2,
                'code3' => $this->code3,
            ])->count() > 0;
    }

}
