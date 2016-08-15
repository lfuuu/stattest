<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\TariffVoip;
use yii\helpers\ArrayHelper;

/**
 * @method static TariffVoipDao me($args = null)
 * @property
 */
class TariffVoipDao extends Singleton
{
    public function getMainList($isWithEmpty = false, $connectingPointId = false, $currencyId = false, $status = false)
    {
        $query = TariffVoip::find();
        $query->andWhere(['dest' => 4]);
        if ($connectingPointId !== false) {
            $query->andWhere(['connection_point_id' => $connectingPointId]);
        }
        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }
        if ($status !== false) {
            $query->andWhere(['status' => $status]);
        }

        $list =
            $query
                ->orderBy([
                    'is_default' => SORT_DESC,
                    'status' => SORT_ASC,
                    'month_line' => SORT_ASC,
                    'month_min_payment' => SORT_ASC,
                    'id' => SORT_DESC,
                ])
                ->asArray()
                ->all();


        $result = [];
        foreach ($list as $tariff) {
            $result[$tariff['id']] = $tariff['name'] . ' (' . $tariff['month_number'] . '-' . $tariff['month_line'] . ')';
        }

        if ($isWithEmpty) {
            $result = ['' => '----'] + $result;
        }

        return $result;
    }

    public function getLocalMobList($isWithEmpty = false, $connectingPointId = false, $currencyId = false)
    {
        $query = TariffVoip::find();
        $query->andWhere(['dest' => 5]);
        //$query->andWhere(['status' => 'public']);

        if ($connectingPointId !== false) {
            $query->andWhere(['connection_point_id' => $connectingPointId]);
        }
        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('status, month_min_payment')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }

    public function getRussiaList($isWithEmpty = false, $connectingPointId = false, $currencyId = false)
    {
        $query = TariffVoip::find();
        $query->andWhere(['dest' => 1]);
        //$query->andWhere(['status' => 'public']);

        if ($connectingPointId !== false) {
            $query->andWhere(['connection_point_id' => $connectingPointId]);
        }
        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('status, month_min_payment')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }

    public function getInternList($isWithEmpty = false, $connectingPointId = false, $currencyId = false)
    {
        $query = TariffVoip::find();
        $query->andWhere(['dest' => 2]);
        //$query->andWhere(['status' => 'public']);

        if ($connectingPointId !== false) {
            $query->andWhere(['connection_point_id' => $connectingPointId]);
        }
        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('status, month_min_payment')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }

    public function getSngList($isWithEmpty = false, $connectingPointId = false, $currencyId = false)
    {
        $query = TariffVoip::find();
        $query->andWhere(['dest' => 3]);
        //$query->andWhere(['status' => 'public']);

        if ($connectingPointId !== false) {
            $query->andWhere(['connection_point_id' => $connectingPointId]);
        }
        if ($currencyId !== false) {
            $query->andWhere(['currency_id' => $currencyId]);
        }

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('status, month_min_payment')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }
        return $list;
    }
}