<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Мейджер (NNP-фильтр)
 *
 * @property int $id
 * @property string $name
 * @property int $order
 * @property int $country_code
 * @property int $major_group_id
 * @property string $nnp_filter_json
 */
class Major extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
        ];
    }

    /**
     * Имя таблицы
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.major';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
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

    public function __toString()
    {
        return $this->name;
    }

}