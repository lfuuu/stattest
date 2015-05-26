<?php
namespace app\models;

use app\classes\bill\VoipBiller;
use yii\db\ActiveRecord;
use app\queries\UsageVoipQuery;
use DateTime;
use app\models\TariffVoip;
use app\models\VoipNumber;

/**
 * @property int $id
 *
 * @property Region $connectionPoint
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

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    public function isActive()
    {
        $timezone = $this->clientAccount->timezone;

        $now = new DateTime('now', $timezone);

        $actualFrom = new DateTime($this->actual_from, $timezone);
        $actualTo = new DateTime($this->actual_to, $timezone);
        $actualTo->setTime(23, 59, 59);

        return $actualFrom <= $now and $actualTo >= $now;
    }

    public function getVoipNumber()
    {
        return $this->hasOne(VoipNumber::className(), ['number' => 'E164']);
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
}

