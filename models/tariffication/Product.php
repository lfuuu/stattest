<?php

namespace app\models\tariffication;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 */
class Product extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_product';
    }
}