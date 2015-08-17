<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\Country;
use app\models\TariffNumber;
use yii\helpers\ArrayHelper;

/**
 * @method static TariffNumberDao me($args = null)
 * @property
 */
class TariffNumberDao extends Singleton
{
    public function getList($withEmpty = false, $countryId = false, $currencyId = false, $cityId = false)
    {
        $query = TariffNumber::find();
        if ($countryId !== false) {
            $query->andWhere(['country_id' => $countryId]);
        }
        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }
        if ($cityId !== false) {
            $query->andWhere(['city_id' => $cityId]);
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
            $list = ['' => '-- DID группа --'] + $list;
        }
        return $list;
    }
}