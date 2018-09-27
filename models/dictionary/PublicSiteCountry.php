<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;
use app\models\Country;

/**
 * @property int $id
 * @property int $site_id
 * @property int $country_code
 * @property int $order
 *
 * @property-read PublicSiteCity[] $publicSiteCities
 * @property-read Country $country
 */
class PublicSiteCountry extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'public_site_country';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublicSiteCities()
    {
        return $this->hasMany(PublicSiteCity::class, ['public_site_country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

}