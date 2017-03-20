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
 * @property int $region
 * @property int $amount
 * @property string $comment
 * @property TariffVirtpbx $tariff
 * @property ClientAccount $clientAccount
 * @property UsageVirtpbxHelper $helper
 */
class UsageVirtpbx extends ActiveRecord implements UsageInterface, UsageLogTariffInterface
{

    use LogTariffTrait;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
            'UpdateTask' => [
                'class' => \app\classes\behaviors\UpdateTask::className(),
                'model' => self::tableName(),
            ]
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_virtpbx';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return VirtpbxServiceDao
     */
    public static function dao()
    {
        return VirtpbxServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return VirtpbxBiller
     */
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
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_VIRTPBX;
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
     * @param UsageInterface $usage
     * @return VirtpbxServiceTransfer
     */
    public static function getTransferHelper($usage = null)
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

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoLogTariff(self::className());
    }

    /**
     * Услуга помечена как переносимая на новый ЛС?
     *
     * @param bool|null $isAdd это добавляемая услуга
     * @return bool
     */
    public function isTransfered($isAdd = null)
    {
        if ($isAdd === null) {
            return (bool)($this->prev_usage_id || $this->next_usage_id);
        }

        return (bool)($isAdd ? $this->prev_usage_id : $this->next_usage_id);
    }

}
