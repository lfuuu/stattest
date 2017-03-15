<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int number_range_id
 * @property int prefix_id
 * 
 * @property NumberRange numberRange
 * @property Prefix prefix
 */
class NumberRangePrefix extends ActiveRecord
{
    // Методы для полей insert_time, insert_user_id
    use \app\classes\traits\InsertUserTrait;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'number_range_id' => 'Диапазон номеров',
            'prefix_id' => 'Префикс',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.number_range_prefix';
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
     * @return ActiveQuery
     */
    public function getNumberRange()
    {
        return $this->hasOne(NumberRange::className(), ['id' => 'number_range_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrefix()
    {
        return $this->hasOne(Prefix::className(), ['id' => 'prefix_id']);
    }
}