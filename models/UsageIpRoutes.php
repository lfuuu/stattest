<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property integer $id
 * @property string $activation_dt
 * @property string $expire_dt
 * @property string $actual_from
 * @property string $actual_to
 * @property integer $port_id
 * @property string $net
 * @property string $nat_net
 * @property string $dnat
 * @property string $type
 * @property string $up_node
 * @property string $flows_node
 * @property string $comment
 * @property integer $gpon_reserv
 */
class UsageIpRoutes extends ActiveRecord
{

    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::class,
        ];
    }

    public static function tableName()
    {
        return 'usage_ip_routes';
    }
}