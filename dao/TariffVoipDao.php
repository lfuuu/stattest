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
    public function getMainList($withEmpty = false, $connectingPointId = false, $currencyId = false, $status = false)
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
            ArrayHelper::map(
                $query
                    ->orderBy('status, month_line, month_min_payment')
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

    public function getLocalMobList($withEmpty = false, $connectingPointId = false, $currencyId = false)
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
        if ($withEmpty) {
            $list = ['' => '-- Тариф --'] + $list;
        }
        return $list;
    }

    public function getRussiaList($withEmpty = false, $connectingPointId = false, $currencyId = false)
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
        if ($withEmpty) {
            $list = ['' => '-- Тариф --'] + $list;
        }
        return $list;
    }

    public function getInternList($withEmpty = false, $connectingPointId = false, $currencyId = false)
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
        if ($withEmpty) {
            $list = ['' => '-- Тариф --'] + $list;
        }
        return $list;
    }

    public function getSngList($withEmpty = false, $connectingPointId = false, $currencyId = false)
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
        if ($withEmpty) {
            $list = ['' => '-- Тариф --'] + $list;
        }
        return $list;
    }
}