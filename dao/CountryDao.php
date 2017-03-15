<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Country;

/**
 * @method static CountryDao me($args = null)
 */
class CountryDao extends Singleton
{

    /**
     * @param int $countryCode
     * @return string
     */
    public function getNameByCode($countryCode)
    {
        if (!$countryCode) {
            return '';
        }

        $country = Country::findOne(['code' => $countryCode]);
        return $country ? $country->name : '';
    }

}