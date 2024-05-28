<?php

namespace app\models;

use app\classes\behaviors\ModelLifeRecorder;
use app\classes\model\ActiveRecord;

/**
 * @property string $payment_id
 * @property string $type
 * @property string $comment
 */
class PaymentInfoShort extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newpayment_info_short';
    }


    public static function primaryKey()
    {
        return ['payment_id'];
    }

    /**
     * Поведение
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'ModelLifeRec' => [
                'class' => ModelLifeRecorder::class,
                'modelName' => 'payment_unspecified',
            ]
        ];
    }
}
