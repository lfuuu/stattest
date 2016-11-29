<?php
namespace app\models;

use DateTime;
use yii\db\ActiveRecord;
use app\classes\bill\SmsBiller;
use app\classes\transfer\SmsServiceTransfer;
use app\dao\services\SmsServiceDao;
use app\queries\UsageQuery;
use app\classes\monitoring\UsagesLostTariffs;
use app\helpers\usages\UsageSmsHelper;
use app\models\usages\UsageInterface;
use app\classes\traits\UsageTrait;

/**
 * @property int $id
 * @property TariffSms $tariff
 * @property UsageSmsHelper $helper
 */
class UsageSms extends ActiveRecord implements UsageInterface
{

    use UsageTrait;

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
        return 'usage_sms';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return SmsServiceDao
     */
    public static function dao()
    {
        return SmsServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return SmsBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new SmsBiller($this, $date, $clientAccount);
    }

    /**
     * @return TariffSms
     */
    public function getTariff()
    {
        return $this->hasOne(TariffSms::className(), ['id' => 'tarif_id']);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_SMS;
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
     * @param $usage
     * @return SmsServiceTransfer
     */
    public static function getTransferHelper($usage = null)
    {
        return new SmsServiceTransfer($usage);
    }

    /**
     * @return UsageSmsHelper
     */
    public function getHelper()
    {
        return new UsageSmsHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffSms::tableName());
    }

}
