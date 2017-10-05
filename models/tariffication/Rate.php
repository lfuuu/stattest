<?php

namespace app\models\tariffication;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $feature_id
 */
class Rate extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_rate';
    }
}