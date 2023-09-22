<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use yii;

/**
 * @property string $flag
 * @property string $value
 *
 */
class EventFlag extends ActiveRecord
{
    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'flag' => 'Ключ',
            'value' => 'Значение',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'event.flag';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        if (!empty(Yii::$app->dbPg)) {
            return Yii::$app->dbPg;
        }
    }


    public static function upsert($flag, $value, $inConflictUpdate = true)
    {
        $table = self::tableName();

        $query = self::getDb()->createCommand($sql = "INSERT INTO {$table} (flag, value) VALUES (:flag, :value) ON CONFLICT(flag) ".
            "DO ".($inConflictUpdate ? "UPDATE SET value=EXCLUDED.value" : "NOTHING"),
            ['flag' => $flag, 'value' => $value]
        );

        echo PHP_EOL . $query->rawSql;

        $result = $query->execute();

        return $result;
    }

    public static function getOrNull($flag)
    {
        return EventFlag::find()->where(['flag' => $flag])->select('value')->scalar();
    }

}
