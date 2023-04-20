<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\dao\CurrencyRateDao;
use app\queries\CurrencyRateQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $date           дата yyyy-mm-dd
 * @property string $currency       валюта: USD, RUB
 * @property float $rate           значение курса на дату
 */
class CurrencyRate extends ActiveRecord
{
    public const transferFee = 0.02; // % => value
    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Дата',
            'currency' => 'Валюта',
            'rate' => 'Курс',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'currency_rate';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => null,
                'value' => new Expression('UTC_TIMESTAMP()'),
            ]
        ];
    }

    /**
     * @return CurrencyRateDao
     */
    public static function dao()
    {
        return CurrencyRateDao::me();
    }

    /**
     * @return CurrencyRateQuery
     */
    public static function find()
    {
        return new CurrencyRateQuery(get_called_class());
    }
}