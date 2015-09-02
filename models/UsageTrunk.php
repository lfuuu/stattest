<?php
namespace app\models;

use yii\db\ActiveRecord;
use DateTime;
use app\classes\transfer\TrunkServiceTransfer;
use app\dao\services\TrunkServiceDao;

/**
 * @property int    $id
 * @property int    $client_account_id
 * @property int    $connection_point_id
 * @property int    $trunk_id
 * @property string $actual_from
 * @property string $actual_to
 * @property string $activation_dt
 * @property string $expire_dt
 * @property int    $orig_enabled
 * @property int    $term_enabled
 * @property int    $orig_min_payment
 * @property int    $term_min_payment
 * @property string $description
 * @property int    $operator_id
 *
 * @property ClientAccount $clientAccount
 * @property Region $connectionPoint
 * @property
 */
class UsageTrunk extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_trunk';
    }

    public static function dao()
    {
        return TrunkServiceDao::me();
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_TRUNK;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_account_id']);
    }

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::className(), ['id' => 'connection_point_id']);
    }

    public function isActive()
    {
        $now = new DateTime('now');

        $activationDt = new DateTime($this->activation_dt);
        $expireDt = new DateTime($this->expire_dt);

        return $activationDt <= $now and $expireDt >= $now;
    }

    public static function getTransferHelper($usage)
    {
        return new TrunkServiceTransfer($usage);
    }

}

