<?php

namespace app\models;

use app\classes\behaviors\ModelLifeRecorder;
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
                'modelName' => 'payment_bank',
            ]
        ];
    }
    public static function getInfoText(Payment $payment, self $i = null)
    {
        $res = '';
        if ($payment->type == Payment::TYPE_BANK) {
            if (!$i) {
                $i = $payment->info;
            }
            if ($i && $i->payer_account) {
                $res = <<<PAY
<small><i>Банковский платеж:</i></small>

Плательшик:
    {$i->payer}
    ИНН: {$i->payer_inn}
    Банк: {$i->payer_bank} ({$i->payer_bik})
    р/с: {$i->payer_account}
PAY;

                if ($i->comment) {
                    $res .= "\n\nКомментарий: {$i->comment}\n";
                }

                if ($i->getter) {
                    $res .= <<<PAY
<small>
Получатель: 
    {$i->getter}
    ИНН: {$i->getter_inn}
    Банк: {$i->getter_bank} ({$i->getter_bik})
    р/с: {$i->getter_account}
    </small>
PAY;

                    if (!$payment->comment && $i->comment) {
                        $payment->comment = 'Платеж #' . $payment->payment_no . ': ' . $i->comment;
                    }
                }
            }
        } elseif ($payment->type == Payment::TYPE_API) {
            $res = var_export(json_decode($payment->apiInfo->info_json, true), true);
        }

        if (!$payment->comment && $res) {
            $payment->comment = '...';
        }

        return $res;
    }
}
