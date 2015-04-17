<?php
namespace app\models;

use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id
 *
 * @property Region $connectionPoint
 * @property
 */
class UsageTrunk extends ActiveRecord
{
    public static function tableName()
    {
        return 'usage_trunk';
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
}

