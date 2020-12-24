<?php

namespace app\modules\sim\filters;

use app\modules\sim\models\Registry;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для RegionSimHistory
 */
class RegistryFilter extends Registry
{
    public $sort;

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function search()
    {
        $query = self::find();

        $tableName = self::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->sort) {
            $query->addOrderBy(['id' => SORT_DESC]);
        }

        return $dataProvider;
    }
}
