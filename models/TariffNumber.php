<?php
namespace app\models;

use app\dao\TariffNumberDao;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $country_id
 * @property int $currency_id
 * @property int $city_id
 * @property string $name
 * @property string $status
 * @property float $activation_fee
 *
 * @property string $period
 * @property int $did_group_id
 *
 * @property Country $country
 * @property City $city
 * @property DidGroup $didGroup
 */
class TariffNumber extends ActiveRecord
{
    const STATUS_PUBLIC = 'public';
    const STATUS_SPECIAL = 'special';
    const STATUS_ARCHIVE = 'archive';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tarifs_number';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_id' => 'Страна',
            'city_id' => 'Город',
            'name' => 'Название',
            'status' => 'Статус',
            'activation_fee' => 'Цена подключения',
            'currency_id' => 'Валюта',
        ];
    }
    /**
     * @return TariffNumberDao
     */
    public static function dao()
    {
        return TariffNumberDao::me();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDidGroup()
    {
        return $this->hasOne(DidGroup::className(), ['id' => 'did_group_id']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/tariff/number/edit', 'id' => $id]);
    }
}