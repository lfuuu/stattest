<?php
namespace app\models;

use DateTime;
use yii\db\ActiveRecord;
use app\classes\bill\VirtpbxBiller;
use app\classes\transfer\VirtpbxServiceTransfer;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\VirtpbxServiceDao;
use app\queries\UsageQuery;
use app\models\usages\UsageInterface;
use app\models\usages\UsageLogTariffInterface;
use app\helpers\usages\UsageVirtpbxHelper;
use app\helpers\usages\LogTariffTrait;

/**
 * @property int $id

 * @property TariffVirtpbx $tariff
 * @property
 */
class UsageVirtpbx extends ActiveRecord implements UsageInterface, UsageLogTariffInterface
{

    use LogTariffTrait;

    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

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
