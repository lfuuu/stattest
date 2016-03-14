<?php
namespace app\models;

use app\dao\CityDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property int $connection_point_id
 * @property string $voip_number_format
 * @property Country $country
 */
class City extends ActiveRecord
{
    const DEFAULT_USER_CITY_ID = 7495; // Moscow

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'country_id' => 'Страна',
            'connection_point_id' => 'Точка подключения',
            'voip_number_format' => 'Формат номеров',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * @return CityDao
     */
    public static function dao()
    {
        return CityDao::me();
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
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'connection_point_id']);
    }

}