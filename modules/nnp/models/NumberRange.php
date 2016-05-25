<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int id
 * @property int country_code
 * @property int ndc
 * @property int number_from
 * @property int number_to
 * @property string operator_source
 * @property int operator_id
 * @property string region_source
 * @property int region_id
 * @property bool is_mob true - DEF, false - ABC
 * @property bool is_active
 */
class NumberRange extends ActiveRecord
{
    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

    // дубль из models/Country.php
    const COUNTRY_CODE_RUSSIA = 643;
    const COUNTRY_CODE_HUNGARY = 348;
    const COUNTRY_CODE_GERMANY = 276;

    /**
     * имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Страна', // country.code. Не путайте с префиксом!
            'ndc' => 'ABC/DEF код',
            'number_from' => 'Номер от',
            'number_to' => 'Номер до',
            'operator_source' => 'Исходный оператор',
            'operator_id' => 'Оператор',
            'region_source' => 'Исходный регион',
            'region_id' => 'Регион',
            'is_mob' => 'ABC / DEF',
            'is_active' => 'Вкл.',

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
            [['operator_id', 'region_id'], 'integer'],
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
}
