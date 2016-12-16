<?php
namespace app\models\flows;

use yii\db\ActiveRecord;

/**
 * Class TraffFlow1h
 * @property  string datetime
 * @property  string router_ip
 * @property  string ip_addr
 * @property  integer in_bytes
 * @property  integer out_bytes
 * @property  integer type
 * @package app\models\flows
 */
class TraffFlow1h extends ActiveRecord
{

    public static function tableName()
    {
        return 'flows.traf_flow_1h';
    }

    public static function getDb()
    {
        return \Yii::$app->dbPgNfDump;
    }

}
