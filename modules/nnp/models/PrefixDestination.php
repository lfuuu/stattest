<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\db\ActiveQuery;

/**
 * @property int $prefix_id
 * @property int $destination_id
 * @property bool $is_addition true - сложить, false - вычесть префикс
 *
 * @property Prefix $prefix
 * @property Destination $destination
 */
class PrefixDestination extends ActiveRecord
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
            'prefix_id' => 'Префикс',
            'destination_id' => 'Направление',
            'is_addition' => 'Операция',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.prefix_destination';
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
        return $this->hasOne(Destination::className(), ['id' => 'destination_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrefix()
    {
        return $this->hasOne(Prefix::className(), ['id' => 'prefix_id']);
    }
}