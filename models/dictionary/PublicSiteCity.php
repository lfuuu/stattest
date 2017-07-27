<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;

/**
 * @property int $public_site_country_id
 * @property int $city_id
 */
class PublicSiteCity extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'public_site_city';
    }

}