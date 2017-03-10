<?php
namespace app\models;

use app\dao\DidGroupDao;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property int $beauty_level
 * @property int country_code
 * @property int city_id
 * @property int number_type_id
 * @property float price1
 * @property float price2
 * @property float price3
 *
 * @property City $city
 * @property Country $country
 */
class DidGroup extends ActiveRecord
{
    const MOSCOW_STANDART_GROUP_ID = 2;

    const BEAUTY_LEVEL_STANDART = 0;
    const BEAUTY_LEVEL_PLATINUM = 1;
    const BEAUTY_LEVEL_GOLD = 2;
    const BEAUTY_LEVEL_SILVER = 3;
    const BEAUTY_LEVEL_BRONZE = 4;

    public static $beautyLevelNames = [
        self::BEAUTY_LEVEL_STANDART => 'Стандартный',
        self::BEAUTY_LEVEL_BRONZE => 'Бронзовый',
        self::BEAUTY_LEVEL_SILVER => 'Серебряный',
        self::BEAUTY_LEVEL_GOLD => 'Золотой',
        self::BEAUTY_LEVEL_PLATINUM => 'Платиновый',
    ];

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Страна',
            'city_id' => 'Город',
            'name' => 'Название',
            'beauty_level' => 'Красивость',
            'number_type_id' => 'Тип номера',
            'price1' => 'Цена 1',
            'price2' => 'Цена 2',
            'price3' => 'Цена 3',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['beauty_level', 'city_id', 'number_type_id', 'country_code'], 'integer'],
            [['name', 'beauty_level', 'country_code', 'number_type_id'], 'required'],
            [['price1', 'price2', 'price3'], 'number'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'did_group';
    }

    /**
     * @return DidGroupDao
     */
    public static function dao()
    {
        return DidGroupDao::me();
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
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
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
     * @param integer $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/tariff/did-group/edit', 'id' => $id]);
    }
}