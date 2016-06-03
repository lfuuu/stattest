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
     * @param int $cityId
     * @return string[]
     */
    public function getList($isWithEmpty = false, $cityId = null)
    {
        $list = self::getDidGroupMapByCityId($cityId, 'id', 'name');

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }

    public function getDidGroupMapByCityId($cityId = null, $keyField = 'beauty_level', $valueField = 'id')
    {
        $query = DidGroup::find();

        if ($cityId) {
            $query->andWhere(['city_id' => $cityId]);
        }

        return
            ArrayHelper::map(
                (array)$query
                    ->select([
                        $keyField,
                        $valueField
                    ])
                    ->orderBy([
                        $valueField => SORT_ASC,
                    ])
                    ->asArray()
                    ->all(),
                $keyField,
                $valueField
            );
    }

    /**
     * Вернуть список красивостей
     * @param bool $isWithEmpty
     * @return string[]
     */
    public static function getBeautyLevelList($isWithEmpty = false)
    {
        $list = DidGroup::$beautyLevelNames;

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }
}
