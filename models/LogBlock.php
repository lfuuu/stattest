<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class LogBlock extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_block';
    }
}