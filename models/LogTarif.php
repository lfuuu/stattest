<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class LogTarif
 *
 * @property int $id
 * @property string $service
 * @property int $id_service
 * @property int $id_tarif
 * @property int $id_user
 * @property string $ts
 * @property string $comment
 * @property string $date_activation
 * @property int $minpayment_group
 * @property int $minpayment_local_mob
 * @property int $minpayment_russia
 * @property int $minpayment_intern
 * @property int $minpayment_sng
 * @property int $id_tarif_local_mob
 * @property int $id_tarif_russia
 * @property int $id_tarif_russia_mob
 * @property int $id_tarif_intern
 * @property int $id_tarif_sng
 * @property int $dest_group
 * @property-read UsageVoip $usageVoip
 * @property-read TariffVoip $voipTariffMain
 * @property-read TariffVoip $voipTariffLocalMob
 * @property-read TariffVoip $voipTariffRussia
 * @property-read TariffVoip $voipTariffRussiaMob
 * @property-read TariffVoip $voipTariffIntern
 * @property-read TariffInternet $internetTariff
 * @property-read User $user
 */
class LogTarif extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'log_tarif';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsageVoip()
    {
        return $this->hasOne(UsageVoip::className(), ['id' => 'id_service']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoipTariffMain()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInternetTariff()
    {
        return $this->hasOne(TariffInternet::className(), ['id' => 'id_tarif']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoipTariffLocalMob()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_local_mob']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoipTariffRussia()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_russia']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoipTariffRussiaMob()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_russia_mob']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoipTariffIntern()
    {
        return $this->hasOne(TariffVoip::className(), ['id' => 'id_tarif_intern']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_user']);
    }

}