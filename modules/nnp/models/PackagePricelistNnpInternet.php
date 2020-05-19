<?php

namespace app\modules\nnp\models;

class PackagePricelistNnpInternet extends PackagePricelistNnp
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.package_data';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'nnp_pricelist_id', 'bytes_amount'], 'required'],
            [['tariff_id', 'nnp_pricelist_id', 'bytes_amount'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
            'bytes_amount' => 'Трафик (Мб)'
            ];

    }

}