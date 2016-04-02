<?php
namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $voip_number_type_id
 * @property int $country_id
 *
 * @property NumberType $numberType
 * @property Country $country
 *
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=10715171
 */
class NumberTypeCountry extends ActiveRecord
{
    public static function tableName()
    {
        return 'voip_number_type_country';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'voip_number_type_id' => 'Тип номера',
            'country_id' => 'Страна',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['voip_number_type_id', 'country_id'], 'integer'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getNumberType()
    {
        return $this->hasOne(NumberType::className(), ['id' => 'voip_number_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return $this->country->name;
    }

}