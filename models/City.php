<?php
namespace app\models;

use app\dao\CityDao;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property int $connection_point_id
 * @property string $voip_number_format
 *
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
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'voip_number_format'], 'string'],
            [['id', 'country_id', 'connection_point_id'], 'integer'],
            [['name', 'voip_number_format', 'country_id', 'connection_point_id', 'id'], 'required'],
        ];
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
        return Url::to(['/dictionary/city/edit', 'id' => $this->id]);
    }
}