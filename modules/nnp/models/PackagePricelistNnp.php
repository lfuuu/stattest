<?php

namespace app\modules\nnp\models;

class PackagePricelistNnp extends PackagePricelist
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'nnp_pricelist_id'], 'required'],
            [['tariff_id', 'nnp_pricelist_id', 'minute','is_inversion_mgp'], 'integer'],
        ];
    }
}