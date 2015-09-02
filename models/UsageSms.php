<?php
namespace app\models;

use app\classes\bill\SmsBiller;
use app\classes\transfer\SmsServiceTransfer;
use app\dao\services\SmsServiceDao;
use app\queries\UsageQuery;
use app\models\TariffSms;
use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id

 * @property TariffSms $tariff
 * @property
 */
class UsageSms extends ActiveRecord implements Usage
{
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

    public function getCurrentTariff()
    {
        $tariff = TariffSms::findOne($this->tarif_id);
        return $tariff;
    }

    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public static function getTransferHelper($usage)
    {
        return new SmsServiceTransfer($usage);
    }

}
