<?php
namespace app\models;

use app\dao\CourierDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $enabled
 * @property
 */
class Courier extends ActiveRecord
{
    public static function tableName()
    {
        return 'courier';
    }

    public static function dao()
    {
        return CourierDao::me();
    }
}