<?php

namespace app\models\tariffication;

use app\classes\model\ActiveRecord;

/**
 * @property string $id
 * @property string $name
 */
class ServiceType extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tariffication_service_type';
    }
}