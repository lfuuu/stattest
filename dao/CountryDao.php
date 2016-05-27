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
    public function getNameByCode($countryCode)
    {
        if ($countryCode == 0) {
            return '';
        }

        $country = Country::findOne(['code' => $countryCode]);
        return $country ? $country->name : '';
    }

    public function getList($isWithEmpty = false)
    {
        $list =
            ArrayHelper::map(
                Country::find()
                    ->andWhere(['in_use' => 1])
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'code',
                'name'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }

}