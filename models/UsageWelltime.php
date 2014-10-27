<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class UsageWelltime extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_welltime';
    }
}