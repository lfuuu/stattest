<?php

namespace app\forms\tariff;

use app\models\DidGroup;
use app\models\DidGroupPriceLevel;
use app\modules\nnp\models\NdcType;

class DidGroupFormNew extends DidGroupForm
{
    /**
     * @return DidGroup
     */
    public function getDidGroupModel()
    {
        $model = new DidGroup();
        $model->ndc_type_id = NdcType::ID_GEOGRAPHIC;

        return $model;
    }

    /**
     * @return DidGroupPriceLevel[]
     */
    public function getDidGroupPriceLevels()
    {
    }
}
