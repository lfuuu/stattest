<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\DidGroup;
use yii\helpers\ArrayHelper;

/**
 * @method static DidGroupDao me($args = null)
 * @property
 */
class DidGroupDao extends Singleton
{

    public function getList($withEmpty = false, $cityId = false)
    {
        $query = DidGroup::find();

        if ($cityId !== false) {
            $query->andWhere(['city_id' => $cityId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('id')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($withEmpty) {
            $list = ['' => '-- DID группа --'] + $list;
        }
        return $list;
    }

}
