<?php
namespace app\models;

use app\classes\bill\WelltimeBiller;
use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id
 *
 * @property TariffExtra $tariff
 * @property
 */
class UsageWelltime extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'usage_welltime';
    }

    public function getBiller(DateTime $date)
    {
        return new WelltimeBiller($this, $date);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffExtra::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_WELLTIME;
    }
}