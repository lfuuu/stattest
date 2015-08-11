<?php
namespace app\dao;

use app\classes\Singleton;
use yii\helpers\ArrayHelper;
use app\models\TariffVoipPackage;

/**
 * @method static TariffVoipPackageDao me($args = null)
 * @property
 */
class TariffVoipPackageDao extends Singleton
{
    public function getMainList($withEmpty = false, $countryId = false, $connectingPointId = false, $currencyId = false)
    {
        $query = TariffVoipPackage::find();

        if ($countryId !== false) {
            $query->andWhere(['country_id' => $countryId]);
        }

        if ($connectingPointId !== false) {
            $query->andWhere(['connection_point_id' => $connectingPointId]);
        }

        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('name asc')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );

        if ($withEmpty) {
            $list = ['' => '-- Тариф --'] + $list;
        }

        return $list;
    }

}