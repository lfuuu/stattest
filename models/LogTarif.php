<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string service
 * @property int id_service
 * @property int id_tarif
 * @property int id_user
 * @property string ts
 * @property string comment
 * @property string date_activation
 * @property UsageVoip $usageVoip
 * @property TariffVoip $voipTariffMain
 * @property TariffVoip $voipTariffLocalMob
 * @property TariffVoip $voipTariffRussia
 * @property TariffVoip $voipTariffRussiaMob
 * @property TariffVoip $voipTariffIntern
 * @property TariffVoip $voipTariffSng
 * @property
 */
class LogTarif extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_tarif';
    }

    public function getUsageVoip()
    {
        return $this->hasOne(UsageVoip::className(), ['id' => 'id_service']);
    }

    public function getVoipTariffMain()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif']);
    }

    public function getVoipTariffLocalMob()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_local_mob']);
    }

    public function getVoipTariffRussia()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_russia']);
    }

    public function getVoipTariffRussiaMob()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_russia_mob']);
    }

    public function getVoipTariffIntern()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_intern']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_user']);
    }

}