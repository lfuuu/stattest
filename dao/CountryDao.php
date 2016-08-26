<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Country;
use yii\helpers\ArrayHelper;

/**
 * @method static CountryDao me($args = null)
 * @property
 */
class CountryDao extends Singleton
{

    /**
     * @param int $countryCode
     * @return string
     */
    public function getNameByCode($countryCode)
    {
        if ($countryCode == 0) {
            return '';
        }

        $country = Country::findOne(['code' => $countryCode]);
        return $country ? $country->name : '';
    }

}