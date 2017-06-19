<?php

namespace tests\codeception\unit\models;

use app\modules\nnp\models\NdcType;
use app\models\UsageVoip;
use app\models\LogTarif;
use app\models\TariffVoip;

class _UsageVoip extends \app\models\UsageVoip
{

    /**
     * Превращение услуги в 7800 + линия без номера
     * @inheritdoc
     */
    public function setLine7800()
    {
        $usageLine = new $this;
        $usageLine->setAttributes($this->getAttributes());
        unset($usageLine->id);
        $usageLine->client = $this->client;
        $usageLine->ndc_type_id = NdcType::ID_MCN_LINE;
        $usageLine->save();

        $this->ndc_type_id = NdcType::ID_FREEPHONE;
        $this->line7800_id = $usageLine->id;
        $this->address = 'test address 7800';
        $this->save();

        $tariffId = TariffVoip::find()->select('MAX(id)')->scalar();

        $logTariff = new LogTarif;
        $logTariff->service = UsageVoip::tableName();
        $logTariff->id_service = $usageLine->id;
        $logTariff->id_tarif = $tariffId;
        $logTariff->date_activation = $this->actual_from;
        $logTariff->save();
    }

}