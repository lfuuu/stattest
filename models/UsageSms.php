<?php
namespace app\models;

use app\classes\bill\SmsBiller;
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
}