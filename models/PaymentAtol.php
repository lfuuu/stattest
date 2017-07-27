<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * @property int $id             идентификатор платежа
 * @property string $uuid          ID в онлайн-кассе
 * @property string $uuid_status   Статус отправки в онлайн-кассу
 * @property string $uuid_log      Лог отправки в онлайн-кассу
 *
 * @property-read Payment $payment
 */
class PaymentAtol extends ActiveRecord
{
    const UUID_STATUS_SENT = 2;
    const UUID_STATUS_SUCCESS = 3;
    const UUID_STATUS_FAIL = 4;

    public static $uuidStatus = [
        self::UUID_STATUS_SENT => 'Отправлен',
        self::UUID_STATUS_SUCCESS => 'Успешно',
        self::UUID_STATUS_FAIL => 'Ошибка',
    ];

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'payment_atol';
    }

    /**
     * @return ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payment::className(), ['id' => 'id']);
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        $uuidStatusToCssClass = [
            PaymentAtol::UUID_STATUS_SENT => 'warning',
            PaymentAtol::UUID_STATUS_SUCCESS => 'success',
            PaymentAtol::UUID_STATUS_FAIL => 'danger',
        ];

        return $uuidStatusToCssClass[$this->uuid_status];
    }
}