<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $payment_id       идентификатор платежа
 * @property string $token_id      ID в онлайн-кассе
 * @property int $account_id       ЛС
 * @property string $created_at    Дата создания
 * @property float $sum            Сумма
 * @property string $currency      Валюта
 * @property string $token_data    Данные плтатежи
 */
class PaymentStripe extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'payment_stripe';
    }

}