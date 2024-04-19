<?php

namespace app\modules\uu\models_light;

use yii\base\Component;
use app\forms\tariff\voip\TariffVoipForm;
use app\forms\tariff\voip_package\TariffVoipPackageForm;
use app\modules\callTracking\models\AccountTariff;
use app\modules\nnp\models\AccountTariffLight;
use app\modules\uu\models\AccountTariffFlat;
use app\modules\uu\models\TariffTags;
use app\modules\uu\models\TariffVoipGroup;

class TariffLight extends Component implements InvoiceLightInterface
{
    /**
     * @return string
     */
    public static function getKey()
    {
        return 'tariff';
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return 'Данные тарифа';
    }

    /**
     * @return array
     */
    public static function attributeLabels()
    {
        return array_merge(
            TariffVoipForm::attributeLabels(),
            TariffVoipPackageForm::attributeLabels(),
            AccountTariff::attributeLabels(),
            AccountTariffLight::attributeLabels(),
            AccountTariffFlat::attributeLabels(),
            TariffTags::attributeLabels(),
            TariffVoipGroup::attributeLabels(),
        );
    }

}
