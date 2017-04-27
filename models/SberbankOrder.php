<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\classes\behaviors\CreatedAt;

/**
 * Class SberbankOrder
 *
 * @property integer id
 * @property string  created_at
 * @property string  order_id
 * @property string  bill_no
 * @property integer payment_id
 * @property integer status
 * @property string  order_url
 * @property string  info_json
 * @property Bill    bill
 */
class SberbankOrder extends ActiveRecord
{
    const STATUS_NOT_REGISTERED = 0;
    const STATUS_REGISTERED = 1;
    const STATUS_PAYED = 2;

    /**
     * Навание таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'order_sberbank';
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::className(),
        ];
    }

    /**
     * Связка со счетом
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::className(), ['bill_no' => 'bill_no']);
    }
}
