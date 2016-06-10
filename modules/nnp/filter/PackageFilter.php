<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\Package;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Package
 */
class PackageFilter extends Package
{
    public $name = '';

    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Package::find();
        $packageTableName = Package::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', $packageTableName . '.name', $this->name]);

        return $dataProvider;
    }
}
