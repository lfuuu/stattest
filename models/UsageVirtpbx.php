<?php
namespace app\models;

use app\classes\bill\VirtpbxBiller;
use app\queries\UsageQuery;
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

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VirtpbxBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffVirtpbx::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_VIRTPBX;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getCurrentTariff()
    {
        $logTariff =
            LogTarif::find()
            ->andWhere(['service' => 'usage_virtpbx', 'id_service' => $this->id])
            ->andWhere('date_activation <= now()')
            ->andWhere('id_tarif != 0')
            ->orderBy('date_activation desc, id desc')
            ->limit(1)
            ->one();
        if ($logTariff === null) {
            return false;
        }

        $tariff = TariffVirtpbx::findOne($logTariff->id_tarif);
        return $tariff;
    }
}
