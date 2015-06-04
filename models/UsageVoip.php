<?php
namespace app\models;

use app\classes\bill\VoipBiller;
use app\classes\transfer\VoipServiceTransfer;
use app\dao\services\VoipServiceDao;
use yii\db\ActiveRecord;
use app\queries\UsageVoipQuery;
use DateTime;
use app\models\TariffVoip;
use app\models\VoipNumber;
use app\models\Datacenter;

/**
 * @property int $id
 * @property
 */
class UsageVoip extends ActiveRecord implements Usage
{
    public static function tableName()
    {
        return 'usage_voip';
    }

    public static function find()
    {
        return new UsageVoipQuery(get_called_class());
    }

    public static function dao()
    {
        return VoipServiceDao::me();
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VoipBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return null;
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_VOIP;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getVoipNumber()
    {
        return $this->hasOne(VoipNumber::className(), ['number' => 'E164']);
    }

    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::className(), ["region" => "region"]);
    }

    public function getCurrentTariff()
    {
        $logTariff =
            LogTarif::find()
            ->andWhere(['service' => 'usage_voip', 'id_service' => $this->id])
            ->andWhere('date_activation <= now()')
            ->andWhere('id_tarif != 0')
            ->orderBy('date_activation desc, id desc')
            ->limit(1)
            ->one();
        if ($logTariff === null) {
            return false;
        }

        $tariff = TariffVoip::findOne($logTariff->id_tarif);
        return $tariff;
    }

    public function getTransferHelper()
    {
        return new VoipServiceTransfer($this);
    }

    public function getTitle()
    {
        return 'Телефония номера';
    }

}

