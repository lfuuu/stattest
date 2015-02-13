<?php
namespace app\models;

use app\classes\bill\IpPortBiller;
use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id
 * @property
 */
class UsageIpPorts extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'usage_ip_ports';
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new IpPortBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return null;
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_IPPORT;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }
}