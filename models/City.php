<?php
namespace app\models;

use app\dao\CityDao;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\classes\traits\GridSortTrait;

/**
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property int $connection_point_id
 * @property string $voip_number_format
 * @property int $in_use
 * @property int $is_show_in_lk
 * @property int $order
 *
 * @property Country $country
 * @property Region $region
 */
class City extends ActiveRecord
{

    use GridSortTrait;

    const DEFAULT_USER_CITY_ID = 7495; // Moscow

    public static $primaryField = 'id';

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'country_id' => 'Страна',
            'connection_point_id' => 'Точка подключения',
            'voip_number_format' => 'Формат номеров',
            'in_use' => 'Есть номера',
            'is_show_in_lk' => 'Показывать в ЛК',
            'billing_method_id' => 'Метод биллингования',
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
            [['id', 'country_id', 'connection_point_id', 'billing_method_id', 'is_show_in_lk'], 'integer'],
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
     * @return \yii\db\ActiveQuery
     */
    public function getBillingMethod()
    {
        return $this->hasOne(CityBillingMethod::className(), ['id' => 'billing_method_id']);
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
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/dictionary/city/edit', 'id' => $id]);
    }
}