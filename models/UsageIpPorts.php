<?php

namespace app\models;

use app\classes\bill\IpPortBiller;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\IpPortsServiceDao;
use app\helpers\usages\LogTariffTrait;
use app\helpers\usages\UsageIpPortsHelper;
use app\models\usages\UsageInterface;
use app\models\usages\UsageLogTariffInterface;
use app\queries\UsageQuery;
use DateTime;

/**
 * @property int $id
 * @property-read UsageIpPortsHelper $helper
 */
class UsageIpPorts extends ActiveRecord implements UsageInterface, UsageLogTariffInterface
{

    use LogTariffTrait;

    public $actual5d;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    /**
     * @return string
     */
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

    /**
     * @return IpPortsServiceDao
     */
    public static function dao()
    {
        return IpPortsServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return IpPortBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new IpPortBiller($this, $date, $clientAccount);
    }

    /**
     * @param string $date
     * @return bool|TariffInternet
     */
    public function getTariff($date = 'now')
    {
        $logTariff = $this->getLogTariff($date);
        if ($logTariff === null) {
            return false;
        }

        return TariffInternet::findOne($logTariff->id_tarif);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_IPPORT;
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    /**
     * @return Region
     */
    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    /**
     * @return TechPort
     */
    public function getPort()
    {
        return $this->hasOne(TechPort::className(), ['id' => 'port_id']);
    }

    /**
     * @return \app\models\UsageTechCpe[]
     */
    public function getCpeList()
    {
        return UsageTechCpe::find()->where([
            'service' => Transaction::SERVICE_IPPORT,
            'id_service' => $this->id
        ])->all();
    }

    /**
     * @return UsageIpRoutes[]
     */
    public function getNetList()
    {
        return UsageIpRoutes::find()
            ->where(['port_id' => $this->id])
            ->all();
    }

    /**
     * @return UsageIpPortsHelper
     */
    public function getHelper()
    {
        return new UsageIpPortsHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoLogTariff(self::className());
    }

}
