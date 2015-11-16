<?php
namespace app\models;

use DateTime;
use app\queries\UsageQuery;
use yii\db\ActiveRecord;
use app\classes\bill\IpPortBiller;
use app\classes\transfer\IpPortsServiceTransfer;
use app\dao\services\IpPortsServiceDao;
use app\classes\monitoring\UsagesLostTariffs;
use app\classes\usages\UsageIpPortsHelper;

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
            ->all();
    }

    /**
     * @param $usage
     * @return IpPortsServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new IpPortsServiceTransfer($usage);
    }

    /**
     * @return UsageIpPortsHelper
     */
    public function getHelper()
    {
        return new UsageIpPortsHelper($this);
    }

    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoLogTariff(self::className());
    }

}
