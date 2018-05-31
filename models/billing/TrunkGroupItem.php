<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property int $trunk_group_id
 * @property int $trunk_id
 * @property int $child_trunk_group_id
 */
class TrunkGroupItem extends ActiveRecord
{
    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trunk_group_id' => 'Группа транков',
            'trunk_id' => 'Транк',
            'child_trunk_group_id' => 'Дочерная группа транков',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_group_item';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }
}