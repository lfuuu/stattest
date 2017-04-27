<?php

/**
 * Class GoodsOrganization
 *
 * @property integer id
 * @property string name
 * @property string jur_name
 * @property string jur_name_full
 */
class GoodsOrganization extends ActiveRecord\Model
{
    static $table_name = 'g_organization';

    const MCN_TELECOM_KFT = '242268ff-0a4a-11e7-a972-00155d881200';
    const DEFAULT_FOR_INCOMES = 'af714d23-1334-11e0-9c11-d485644c7711'; // Олфонет

    /**
     * Список НДС организаций
     */
    public static function getTaxList()
    {
        $list = [];
        foreach (GoodsOrganization::find('all', ['order' => 'name']) as $goodOrganization) {

            $tax = 18;

            if ($goodOrganization->id == self::MCN_TELECOM_KFT) {
                $tax = 27;
            }

            $list[$goodOrganization->id] = $tax;
        }

        return $list;
    }
}