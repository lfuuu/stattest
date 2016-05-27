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
    public function getList($isWithEmpty = false, $cityId = false)
    {
        $query = DidGroup::find();

        if ($cityId !== false) {
            $query->andWhere(['city_id' => $cityId]);
        }

        $list = self::getDidGroupMapByCityId($cityId, 'id', 'name');

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }

    public function getDidGroupMapByCityId($cityId, $keyField = 'beauty_level', $valueField = 'id')
    {
        return
            ArrayHelper::map(
                (array)DidGroup::find()
                    ->select([
                        $keyField,
                        $valueField
                    ])
                    ->where(['city_id' => $cityId])
                    ->orderBy([
                        $valueField => SORT_ASC,
                    ])->asArray()->all(),
                $keyField,
                $valueField
            );
    }
}
