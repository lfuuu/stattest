<?php

namespace app\modules\nnp\models;
/**
 * Class PackagePricelistNnpInternet
 * @property integer bytes_amount
 */
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
            [['tariff_id', 'nnp_pricelist_id'], 'integer'],
            [['bytes_amount'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'bytes_amount' => 'Трафик (Мб)'
            ];

    }

    public function beforeSave($insert)
    {
        $this->bytes_amount *= (1024 * 1024);

        return parent::beforeSave($insert);
    }

}