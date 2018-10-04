<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property int $num_id
 * @property string $name
 * @property string $name_full
 * @property string $art
 * @property float $price
 * @property int $quantity
 * @property int $quantity_store
 * @property int $producer_id
 * @property string $description
 * @property int $is_service
 * @property int $group_id
 * @property int $division_id
 * @property int $is_allowpricezero
 * @property int $is_allowpricechange
 * @property string $store
 * @property int $ndc
 * @property string $unit_id
 */
class Good extends ActiveRecord
{
    const GOOD_HASP_HL_PRO_USB = 14015;
    const GOOD_WELLTIME_IP_ATC = 10370;

    public static function tableName()
    {
        return 'g_goods';
    }
}