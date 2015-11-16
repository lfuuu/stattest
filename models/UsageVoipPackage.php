<?php
namespace app\models;

use DateTime;
use yii\db\ActiveRecord;
use app\classes\bill\Biller;
use app\classes\bill\VoipPackageBiller;
use app\classes\transfer\ServiceTransfer;
use app\classes\transfer\VoipPackageServiceTransfer;
use app\classes\monitoring\UsagesLostTariffs;
use app\classes\usages\UsageVoipPackageHelper;

/**
 * @property int $id
 *
 * @property Region $connectionPoint
 * @property
 */
class UsageVoipPackage extends ActiveRecord implements Usage
{

    public static function tableName()
    {
        return 'usage_voip_package';
    }

    public function getTariff()
    {
        return $this->hasOne(TariffVoipPackage::className(), ['id' => 'tariff_id']);
    }

    public function getUsageVoip()
    {
        return $this->hasOne(UsageVoip::className(), ['id' => 'usage_voip_id']);
    }

    /**
     * @return Biller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VoipPackageBiller($this, $date, $clientAccount);
    }

    /**
     * @param $usage
     * @return VoipPackageServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new VoipPackageServiceTransfer($usage);
    }

    /**
     * @return UsageVoipPackageHelper
     */
    public function getHelper()
    {
        return new UsageVoipPackageHelper($this);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_VOIP_PACKAGE;
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffVoipPackage::tableName(), 'tariff_id');
    }

}

