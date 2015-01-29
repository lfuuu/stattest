<?php
namespace app\models;

use app\classes\bill\VirtpbxBiller;
use yii\db\ActiveRecord;
use DateTime;

/**
 * @property int $id

 * @property TariffVirtpbx $tariff
 * @property
 */
class UsageVirtpbx extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'usage_virtpbx';
    }

    public function getBiller(DateTime $date)
    {
        return new VirtpbxBiller($this, $date);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffVirtpbx::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_VIRTPBX;
    }
}