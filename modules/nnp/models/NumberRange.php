<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use app\models\City;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int id
 * @property int $country_prefix
 * @property int ndc
 * @property int number_from
 * @property int number_to
 * @property int full_number_from bigint
 * @property int full_number_to bigint
 * @property string operator_source
 * @property int operator_id
 * @property string region_source
 * @property int region_id
 * @property int city_id // индекса и FK нет, потому что таблица городов в другой БД
 * @property bool is_active
 * @property int ndc_type_id
 * @property string date_stop date
 * @property string date_resolution date
 * @property string detail_resolution
 * @property string status_number
 *
 * @property City city
 * @property Operator operator
 * @property Region region
 * @property NumberRangePrefix[] numberRangePrefixes
 * @property NdcType ndcType
 */
class NumberRange extends ActiveRecord
{
    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

    /**
     * имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_prefix' => 'Страна',
            'ndc' => 'NDC',
            'number_from' => 'Номер от',
            'number_to' => 'Номер до',
            'full_number_from' => 'Полный номер от',
            'full_number_to' => 'Полный номер до',
            'operator_source' => 'Исходный оператор',
            'operator_id' => 'Оператор',
            'region_source' => 'Исходный регион',
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'is_active' => 'Вкл.',
            'ndc_type_id' => 'Тип NDC',
            'date_stop' => 'Дата выключения',
            'date_resolution' => 'Дата принятия решения о выделении диапазона',
            'detail_resolution' => 'Комментарий о выделении диапазона',
            'status_number' => 'Статус номера',

            'insert_time' => 'Когда создал',
            'insert_user_id' => 'Кто создал',
            'update_time' => 'Когда редактировал',
            'update_user_id' => 'Кто редактировал',
        ];
    }

    /**
     * имя таблицы
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.number_range';
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
     * @return Connection
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
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/number-range/edit', 'id' => $id]);
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
    public function getOperator()
    {
        return $this->hasOne(Operator::className(), ['id' => 'operator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNdcType()
    {
        return $this->hasOne(NdcType::className(), ['id' => 'ndc_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumberRangePrefixes()
    {
        return $this->hasMany(NumberRangePrefix::className(), ['number_range_id' => 'id']);
    }

}
