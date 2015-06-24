<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property
 */
class Param extends ActiveRecord
{
    public static function tableName()
    {
        return 'params';
    }
}
