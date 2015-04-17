<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Region;
use yii\helpers\ArrayHelper;

/**
 * @method static RegionDao me($args = null)
 * @property
 */
class RegionDao extends Singleton
{

    public function getList($withEmpty = false, $countryId = false)
    {
        $query = Region::find();
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
            $list = ['' => '-- Точка подключения --'] + $list;
        }
        return $list;
    }

}
