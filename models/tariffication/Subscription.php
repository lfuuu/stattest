<?php

namespace app\models\tariffication;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $feature_id
 */
class Subscription extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_subscription';
    }
}