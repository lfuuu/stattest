<?php

namespace tests\codeception\unit\models;

use app\modules\uu\models\Tariff;
use app\models\UsageVoip;
use app\models\LogTarif;
use app\models\TariffVoip;

class _UsageVirtpbx extends \app\models\UsageVirtpbx
{

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        _ClientAccount::$usageId = $this->id;
        return $this->hasOne(_ClientAccount::className(), ['client' => 'client']);
    }

    /**
     * @inheritdoc
     */
    public function createUsageVoip()
    {
        $tariffId = TariffVoip::find()->select('MAX(id)')->scalar();

        $line7800 = new UsageVoip;
        $line7800->actual_from = $this->actual_from;
        $line7800->actual_to = $this->actual_to;
        $line7800->client = $this->client;
        $line7800->type_id = Tariff::NUMBER_TYPE_LINE;
        $line7800->address = 'test address line 7800';
        $line7800->region = 99;
        $line7800->create_params = '';
        $line7800->save();

        $logTariff = new LogTarif;
        $logTariff->service = UsageVoip::tableName();
        $logTariff->id_service = $line7800->id;
        $logTariff->id_tarif = $tariffId;
        $logTariff->date_activation = $line7800->actual_from;
        $logTariff->save();

        $usage = new UsageVoip;
        $usage->actual_from = $this->actual_from;
        $usage->actual_to = $this->actual_to;
        $usage->client = $this->client;
        $usage->type_id = Tariff::NUMBER_TYPE_7800;
        $usage->address = 'test address';
        $usage->E164 = '123456' . mt_rand(0, 9);
        $usage->line7800_id = $line7800->id;
        $usage->region = 99;
        $usage->create_params = '';
        $usage->save();

        $logTariff = new LogTarif;
        $logTariff->service = UsageVoip::tableName();
        $logTariff->id_service = $usage->id;
        $logTariff->id_tarif = $tariffId;
        $logTariff->date_activation = $usage->actual_from;
        $logTariff->save();
    }

}
