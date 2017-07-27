<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class TaxVoipSettings
 *
 * @property int $id
 * @property int $business_id
 * @property int $country_id
 * @property int $is_with_tax
 */
class TaxVoipSettings extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'tax_voip_settings';
    }

}
