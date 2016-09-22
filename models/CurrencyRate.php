<?php
namespace app\models;

use app\dao\CurrencyRateDao;
use app\queries\CurrencyRateQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $date           дата yyyy-mm-dd
 * @property string $currency       валюта: USD, RUB
 * @property float $rate           значение курса на дату
 */
class CurrencyRate extends ActiveRecord
{
    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
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