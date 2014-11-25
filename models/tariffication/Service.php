<?php
namespace app\models\tariffication;

use app\models\tariffication\type\ServiceType;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $name
 * @property string $service_type_id
 * @property
 */
class Service extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_service';
    }

    public function getType()
    {
        return ServiceType::getById($this->service_type_id);
    }
}