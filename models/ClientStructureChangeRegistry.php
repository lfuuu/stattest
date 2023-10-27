<?php

namespace app\models;

use app\classes\behaviors\NotifiedFlagToImportantEvent;
use app\classes\Connection;
use app\classes\model\ActiveRecord;

/**
 * Class ClientStructureChangeRegistry
 *
 * @property integer $id
 * @property string $section
 * @property integer $model_id
 * @property string $created_at
 * ` */
class ClientStructureChangeRegistry extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_structure_change_registry';
    }

    public static function getDb(): Connection
    {
        return \Yii::$app->db2;
    }

    public static function add($section, $value)
    {
        self::getDb()->createCommand(
            'INSERT INTO ' . self::tableName() . ' (section, model_id) VALUES (:section, :value) ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP',
            ['section' => $section, 'value' => $value]
        )->execute();
    }
}
