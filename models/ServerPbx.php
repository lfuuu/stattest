<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property
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
        return $this->hasOne(Datacenter::className(), ["id" => "datacenter_id"]);
    }
}