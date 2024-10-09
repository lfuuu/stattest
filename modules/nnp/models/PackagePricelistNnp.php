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
            [['tariff_id', 'nnp_pricelist_id', 'minute'], 'integer'],
        ];
    }

    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {
            case 'nnp_pricelist_id':
                if ($pricelist = Pricelist::findOne($value)) {
                    return $pricelist->name;
                }
                break;
        }
        return parent::prepareHistoryValue($field, $value);
    }
}