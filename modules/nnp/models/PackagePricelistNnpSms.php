<?php

namespace app\modules\nnp\models;

class PackagePricelistNnpSms extends PackagePricelistNnp
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.package_sms';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'nnp_pricelist_id', 'include_amount'], 'required'],
            [['tariff_id', 'nnp_pricelist_id', 'include_amount'], 'integer'],
        ];
    }
}