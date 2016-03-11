<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\DidGroup;
use Yii;
use yii\helpers\ArrayHelper;

/**
 */
class DidGroupDao extends Singleton
{
    /**
     * Вернуть список
     * @param bool $isWithEmpty
     * @return string[]
     */
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
