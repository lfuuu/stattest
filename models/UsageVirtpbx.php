<?php
namespace app\models;

use DateTime;
use app\classes\bill\VirtpbxBiller;
use app\classes\transfer\VirtpbxServiceTransfer;
use app\dao\services\VirtpbxServiceDao;
use app\queries\UsageQuery;
use yii\db\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\helpers\usages\UsageVirtpbxHelper;
use app\models\usages\UsageInterface;
use app\models\usages\UsageLogTariffInterface;

/**
 * @property int $id

 * @property TariffVirtpbx $tariff
 * @property
 */
class UsageVirtpbx extends ActiveRecord implements UsageInterface, UsageLogTariffInterface
{
    public static function tableName()
    {
        return 'usage_virtpbx';
    }

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public static function dao()
    {
        return VirtpbxServiceDao::me();
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VirtpbxBiller($this, $date, $clientAccount);
    }

    /**
     * @param string $date
     * @return bool|TariffVirtpbx
     */
    public function getTariff($date = 'now')
    {
        $logTariff = $this->getLogTariff($date);
        if ($logTariff === null) {
            return false;
        }

        return TariffVirtpbx::findOne($logTariff->id_tarif);
    }

    /**
     * @param string $date
     * @return null|LogTarif
     */
    public function getLogTariff($date = 'now')
    {
        $date = (new DateTime($date))->format('Y-m-d H:i:s');

        return
            LogTarif::find()
                ->andWhere(['service' => 'usage_virtpbx', 'id_service' => $this->id])
                ->andWhere('date_activation <= :date', [':date' => $date])
                ->andWhere('id_tarif != 0')
                ->orderBy('date_activation desc, id desc')
                ->limit(1)
                ->one();
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_VIRTPBX;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    /**
     * @param $usage
     * @return VirtpbxServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new VirtpbxServiceTransfer($usage);
    }

    /**
     * @return UsageVirtpbxHelper
     */
    public function getHelper()
    {
        return new UsageVirtpbxHelper($this);
    }

    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoLogTariff(self::className());
    }

}
