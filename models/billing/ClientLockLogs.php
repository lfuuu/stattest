<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * Class ClientLockLogs
 *
 * @property $dt
 * @property int $client_id
 * @property int $region_id
 * @property boolean $voip_auto_disabled
 * @property boolean $voip_auto_disabled_local
 * @property boolean $is_overran - суточная / месячная блокировка
 * @property boolean $is_finance_block - финансовая блокировка
 */
class ClientLockLogs extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.clients_locks_logs';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

}