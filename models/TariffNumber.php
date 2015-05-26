<?php
namespace app\models;

use app\dao\TariffNumberDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $country_id
 * @property int $currency_id
 * @property int $city_id
 * @property int $connection_point_id
 * @property string $name
 * @property string $status
 * @property float $activation_fee
 * @property float $periodical_fee
 * @property string $period
 * @property int $did_group_id
 * @property int $old_beauty_level
 * @property int $old_prefix

 * @property Country $country
 * @property City $city
 * @property Region $connectionPoint

 * @method static TariffNumber findOne($condition)
 * @property
 */
class TariffNumber extends ActiveRecord
{
    const STATUS_PUBLIC = 'public';
    const STATUS_SPECIAL = 'special';
    const STATUS_ARCHIVE = 'archive';

    public static function tableName()
    {
        return 'tarifs_number';
    }

    public static function dao()
    {
        return TariffNumberDao::me();
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::className(), ['id' => 'connection_point_id']);
    }

}