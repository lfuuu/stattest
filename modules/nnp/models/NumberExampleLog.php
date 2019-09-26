<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property string $event
 * @property string $inserted_at
 */
class NumberExampleLog extends ActiveRecord
{
    const EVENT_DELETE = 'delete';
    const EVENT_RESET = 'reset';
    const EVENT_INSERT = 'insert';

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
        return 'nnp.number_example_log';
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
        return Yii::$app->dbPgNnp;
    }
}
