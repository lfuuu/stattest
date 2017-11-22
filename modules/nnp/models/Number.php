<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * @property int $full_number bigint
 * @property int $country_code
 *
 * @property string $operator_source
 * @property int $operator_id
 *
 * @property string $region_source
 * @property int $region_id
 *
 * @property string $city_source
 * @property int $city_id
 *
 * @property-read Country $country
 * @property-read Operator $operator
 * @property-read Region $region
 * @property-read City $city
 */
class Number extends ActiveRecord
{
    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'full_number' => 'Полный номер',
            'country_code' => 'Страна',

            'operator_source' => 'Исходный оператор',
            'operator_id' => 'Оператор',

            'region_source' => 'Исходный регион',
            'region_id' => 'Регион',

            'city_source' => 'Исходный город',
            'city_id' => 'Город',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.number';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_id', 'region_id', 'city_id'], 'integer'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->full_number);
    }

    /**
     * @param int $full_number
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($full_number)
    {
        return Url::to(['/nnp/number/edit', 'full_number' => $full_number]);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(Operator::className(), ['id' => 'operator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }
}
