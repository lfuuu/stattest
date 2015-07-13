<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\City;
use app\models\UsageVoip;
use yii\helpers\ArrayHelper;

/**
 * @method static CityDao me($args = null)
 * @property
 */
class CityDao extends Singleton
{
    public function getList($withEmpty = false, $countryId = false)
    {
        $query = City::find();
        if ($countryId !== false) {
            $query->andWhere(['country_id' => $countryId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($withEmpty) {
            $list = ['' => '-- Город --'] + $list;
        }
        return $list;
    }

    public function getListWithCountries($withEmpty = false)
    {
        $list = [];
        if ($withEmpty) {
            $list = ['' => '-- Город --'] + $list;
        }

        $cities =
            City::find()
                ->joinWith('country')
                ->orderBy('name')
                ->all();

        foreach ($cities as $city) {
            $list[$city->id] = $city->name . ' / ' . $city->country->name;
        }

        return $list;
    }
}