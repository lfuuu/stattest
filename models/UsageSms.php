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

/**
 * @property int $id

 * @property TariffSms $tariff
 * @property
 */
class UsageSms extends ActiveRecord implements UsageInterface
{

    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    public static function tableName()
    {
        return 'usage_sms';
    }

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public static function dao()
    {
        return SmsServiceDao::me();
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new SmsBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffSms::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_SMS;
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
     * @return SmsServiceTransfer
     */
    public static function getTransferHelper($usage)
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

    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffSms::tableName());
    }

}
