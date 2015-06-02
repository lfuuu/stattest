<?php
namespace app\models;

use app\classes\bill\IpPortBiller;
use app\classes\transfer\IpPortsServiceTransfer;
use app\dao\services\IpPortsServiceDao;
use app\queries\UsageQuery;
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

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public static function dao()
    {
        return IpPortsServiceDao::me();
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

    public function getCurrentTariff()
    {
        $logTariff =
            LogTarif::find()
            ->andWhere(['service' => 'usage_ip_ports', 'id_service' => $this->id])
            ->andWhere('date_activation <= now()')
            ->andWhere('id_tarif != 0')
            ->orderBy('date_activation desc, id desc')
            ->limit(1)
            ->one();
        if ($logTariff === null) {
            return false;
        }

        $tariff = TariffInternet::findOne($logTariff->id_tarif);
        return $tariff;
    }

    public function getTransferHelper()
    {
        return new IpPortsServiceTransfer($this);
    }
}
