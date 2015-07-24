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
    public $actual5d;

    public static function tableName()
    {
        return 'usage_ip_ports';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        $query = new UsageQuery(get_called_class());
        return $query->select([
            '*',
            'IF(usage_ip_ports.actual_from<=(NOW()+INTERVAL 5 DAY),1,0) AS actual5d',
            'IF((usage_ip_ports.actual_from<=NOW()) and (usage_ip_ports.actual_to>NOW()),1,0) as actual',
        ]);
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

    public function getCurrentLogTariff()
    {
        return LogTarif::find()
            ->andWhere(['service' => 'usage_ip_ports', 'id_service' => $this->id])
            ->andWhere('date_activation <= now()')
            ->andWhere('id_tarif != 0')
            ->orderBy('date_activation desc, id desc')
            ->limit(1)
            ->one();
    }

    public function getCurrentTariff()
    {
        $logTariff = $this->getCurrentLogTariff();
        if ($logTariff === null) {
            return false;
        }

        $tariff = TariffInternet::findOne($logTariff->id_tarif);
        return $tariff;
    }

    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function getPort()
    {
        return $this->hasOne(TechPort::className(), ['id' => 'port_id']);
    }

    public function getCpeList()
    {
        return TechCpe::find()->where(['service' => Transaction::SERVICE_IPPORT, 'id_service' => $this->id])->all();
    }

    public function getNetList()
    {
        return UsageIpRoutes::find()
            ->where(['port_id' => $this->id])
            ->andWhere('actual_from <= NOW()')
            ->andWhere('actual_to >= NOW()')
            ->all();
    }

    public function getTransferHelper()
    {
        return new IpPortsServiceTransfer($this);
    }

    public static function getTypeTitle()
    {
        return 'Интернет';
    }

    public function getTypeDescription()
    {
        return $this->address;
    }
}
