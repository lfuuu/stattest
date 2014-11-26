<?php
namespace app\models\tariffication;

use yii\db\ActiveRecord;

/**
 * @property string $id
 * @property string $name
 * @property
 */
class ServiceType extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_service_type';
    }
}