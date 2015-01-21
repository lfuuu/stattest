<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class LogBill extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_newbills';
    }
}