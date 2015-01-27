<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property
 */
class Saldo extends ActiveRecord
{
    public static function tableName()
    {
        return 'newsaldo';
    }
}