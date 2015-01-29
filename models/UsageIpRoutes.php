<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class UsageIpRoutes extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_ip_routes';
    }
}