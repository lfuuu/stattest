<?php
namespace app\models\dictionary;

use app\models\Country;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $site_id
 * @property int $country_code
 * @property int $order
 *
 * @property PublicSiteCity[] $publicSiteCities
 * @property Country $country
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
        return $this->hasMany(PublicSiteCity::className(), ['public_site_country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
    }

}