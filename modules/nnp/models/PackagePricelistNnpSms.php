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
            ['include_amount', 'default', 'value' => 0],
            [['tariff_id', 'nnp_pricelist_id', 'include_amount'], 'required'],
            [['tariff_id', 'nnp_pricelist_id', 'include_amount'], 'integer'],
        ];
    }
}