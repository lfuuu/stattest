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
 * @property int $destination_id
 * @property int $major_id
 *
 * @property-read Prefix $prefix
 * @property-read Destination $destination
 */
class DestinationMajor extends ActiveRecord
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
            'destination_id' => 'Направление',
            'major_id' => 'NNP-фильтр',
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
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'insert_time',
                'updatedAtAttribute' => false,
                'value' => new Expression("NOW() AT TIME ZONE 'utc'"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
            [
                // Установить "кто создал"
                'class' => AttributeBehavior::class,
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
        return 'nnp.destination_major';
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
    public function getDestination()
    {
        return $this->hasOne(Destination::class, ['id' => 'destination_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrefix()
    {
        return $this->hasOne(Major::class, ['id' => 'major_id']);
    }
}