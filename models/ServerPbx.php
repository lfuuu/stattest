<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 */
class ServerPbx extends ActiveRecord
{
    const MSK_SERVER_ID = 2;

    public static function tableName()
    {
        return 'server_pbx';
    }

    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::class, ["id" => "datacenter_id"]);
    }
}