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
}