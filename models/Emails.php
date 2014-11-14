<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class Emails extends ActiveRecord
{
    public static function tableName()
    {
        return 'emails';
    }
}