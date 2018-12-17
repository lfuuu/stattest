<?php

namespace app\modules\nnp\models;

class PackagePricelistNnpInternet extends PackagePricelistNnp
{
    /**
     * @return array
     */
    public static function tableName()
    {
        return 'billing_uu.package_data';
    }
}