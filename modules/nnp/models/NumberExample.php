<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $country_code
 * @property int $prefix
 * @property int $ndc
 * @property int $ndc_type_id
 *
 * @property int $region_id
 * @property string $region_name
 *
 * @property int $city_id
 * @property string $city_name
 *
 * @property int $operator_id
 * @property string $operator_name
 *
 * @property int $full_number

 * @property int $ranges
 * @property int $numbers
 *
 *
 * @property-read Operator $operator
 * @property-read Region $region
 * @property-read City $city
 * @property-read NdcType $ndcType
 * @property-read Country $country
 */
class NumberExample extends ActiveRecord
{
    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',

            'country_code' => 'Страна',
            'prefix' => 'Префикс',
            'ndc' => 'NDC',
            'ndc_type_id' => 'Тип NDC',

            'region_id' => 'Регион',
            'region_name' => 'Название региона',

            'city_id' => 'Город',
            'city_name' => 'Название города',

            'operator_id' => 'Оператор',
            'operator_name' => 'Название оператора',

            'full_number' => 'Полный номер (пример)',

            'ranges' => 'Кол-во диапазонов',
            'numbers' => 'Кол-во номеров',

//            'ndc_type_name' => 'Название типа номера',
//            'length_min' => 'Минимальная длина номера от регулятора',
//            'length_max' => 'Максимальная длина',
//            'volume' => 'Кол-во выданных номеров',
//            'is_abroad' => 'Доступен ли для звонка из-за зарубежа',
//            'date_of_origin' => 'Дата принятия номерного плана',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.number_example';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_name', 'region_name', 'city_name'], 'string'],
            [['country_code', 'prefix', 'ndc', 'ndc_type_id', 'operator_id', 'region_id', 'city_id', 'numbers', 'ranges'], 'integer'],
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
        return Url::to(['/nnp/number-example/view', 'id' => $id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(Operator::class, ['id' => 'operator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNdcType()
    {
        return $this->hasOne(NdcType::class, ['id' => 'ndc_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * Запуск обновления таблицы
     *
     * * @throws \yii\db\Exception
     */
    public static function renewAll()
    {
        $db = self::getDb();

        $db->createCommand('SELECT nnp.number_example_renew()')->execute();
    }
}
