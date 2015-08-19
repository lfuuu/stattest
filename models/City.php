<?php
namespace app\models;

use app\dao\CityDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property string $connection_point_id
 * @property string $voip_number_format

 * @property Country $country
 * @property
 */
class City extends ActiveRecord
{
    public static function tableName()
    {
        return 'city';
    }

    public static function dao()
    {
        return CityDao::me();
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'connection_point_id']);
    }

}