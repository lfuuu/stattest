<?php
namespace app\models;

use app\classes\bill\WelltimeBiller;
use app\classes\transfer\WelltimeServiceTransfer;
use app\dao\services\WelltimeServiceDao;
use app\queries\UsageQuery;
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

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public static function dao()
    {
        return WelltimeServiceDao::me();
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new WelltimeBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffExtra::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_WELLTIME;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getCurrentTariff()
    {
        $tariff = TariffExtra::findOne($this->tarif_id);
        return $tariff;
    }

    public function getTransferHelper()
    {
        return new WelltimeServiceTransfer($this);
    }
}
