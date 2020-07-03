<?php

namespace app\modules\nnp2\models;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property string $event
 * @property string $inserted_at
 */
class RangeShortLog extends ActiveRecord
{
    const EVENT_START = '--- start';
    const EVENT_FINISH = 'indexes created';

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event' => 'Событие',
            'inserted_at' => 'Время',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp2.range_short_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['event', 'inserted_at'], 'required'],
            [['event'], 'string', 'max' => 10],
            [['inserted_at'], 'safe'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp2;
    }
}
