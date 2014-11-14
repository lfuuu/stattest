<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class UsageIpPorts extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_ip_ports';
    }
}