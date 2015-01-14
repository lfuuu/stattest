<?php
namespace app\models\tariffication;

use app\dao\tariffication\ServiceTypeDao;
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

    public static function dao()
    {
        return ServiceTypeDao::me();
    }
}