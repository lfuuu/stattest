<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\NdcType;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для NdcType
 */
class NdcTypeFilter extends NdcType
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
        $query = NdcType::find();
        $ndcTypeTableName = NdcType::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', $ndcTypeTableName . '.name', $this->name]);

        return $dataProvider;
    }
}
