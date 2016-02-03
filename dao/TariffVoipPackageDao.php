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

        $list = [];
        foreach($query
            ->orderBy('name asc')
            ->all() as $row) {
                $list[$row->id] = $row->name . " (" . 
                    $row->periodical_fee." " . $row->currency->symbol . ", " . 
                    $row->minutes_count . " минут" . 
                    ($row->min_payment ? ", Мин. платеж: " . $row->min_payment . " " .$row->currency->symbol : "") .
                    ")";
            }

        if ($withEmpty) {
            $list = ['' => '-- Тариф --'] + $list;
        }

        return $list;
    }

}
