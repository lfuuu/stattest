<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @property int $number_range_id
 * @property int $prefix_id
 *
 * @property-read NumberRange $numberRange
 * @property-read Prefix $prefix
 */
class NumberRangePrefix extends ActiveRecord
{
    // Методы для полей insert_time, insert_user_id
    use GetInsertUserTrait;

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
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал"
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'insert_time',
                'updatedAtAttribute' => false,
                'value' => new Expression("NOW() AT TIME ZONE 'utc'"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
            [
                // Установить "кто создал"
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'insert_user_id',
                ],
                'value' => Yii::$app->user->getId(),
            ],
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