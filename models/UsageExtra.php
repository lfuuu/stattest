<?php
namespace app\models;

use app\classes\bill\ExtraBiller;
use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id

 * @property TariffExtra $tariff
 * @property
 */
class UsageExtra extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'usage_extra';
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new ExtraBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffExtra::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_EXTRA;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }
}