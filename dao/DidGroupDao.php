<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\DidGroup;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class DidGroupDao
 */
class DidGroupDao extends Singleton
{
    /**
     * Вернуть список
     *
     * @param bool $isWithEmpty
     * @param int $cityId
     * @param null $countryId
     * @return \string[]
     */
    public function getList($isWithEmpty = false, $cityId = null, $countryId = null)
    {
        $query = DidGroup::find();

        if ($cityId) {
            if ($countryId) {
                $query->andWhere([
                    'country_code' => $countryId
                ]);
                $query->andWhere([
                    'OR',
                    ['city_id' => $cityId],
                    ['city_id' => null]
                ]);
            } else {
                $query->andWhere(['city_id' => $cityId]);
            }
        }

        $list = $query->select(['name', 'id'])
            ->orderBy(['name' => SORT_ASC,])
            ->indexBy('id')
            ->column();

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * Вернуть список красивостей
     *
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
