<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $code
 * @property string $timezone_name
 * @property
 */
class Region extends ActiveRecord
{
    public static function tableName()
    {
        return 'regions';
    }
}