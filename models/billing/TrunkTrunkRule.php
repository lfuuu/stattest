<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property int $trunk_id
 * @property int $order
 * @property int $trunk_group_id
 * @property int $number_id_filter_a
 * @property int $number_id_filter_b
 * @property bool $allow
 */
class TrunkTrunkRule extends ActiveRecord
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
            'trunk_id' => 'Транк',
            'order' => 'Позиция',
            'trunk_group_id' => 'Группа транков',
            'number_id_filter_a' => 'A-номера для фильтра',
            'number_id_filter_b' => 'B-номера для фильтра',
            'allow' => 'Разрешение',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.trunk_trunk_rule';
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
