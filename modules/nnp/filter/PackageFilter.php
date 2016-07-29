<?php

namespace app\modules\nnp\filter;

use app\classes\traits\GetListTrait;
use app\classes\uu\model\Tariff;
use app\modules\nnp\models\Package;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Package
 */
class PackageFilter extends Package
{
    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Package::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
