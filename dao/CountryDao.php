<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ClientCounter;
use app\models\Courier;
use yii\helpers\ArrayHelper;

/**
 * @method static CountryDao me($args = null)
 * @property
 */
class CountryDao extends Singleton
{
    public function getNameByCode($countryCode)
    {
        if ($countryCode == 0) {
            return '';
        }

        $country = Courier::findOne(['code' => $countryCode]);
        return $country ? $country->name : '';
    }

}