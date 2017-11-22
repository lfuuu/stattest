<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\NdcType;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для NdcType
 */
class NdcTypeFilter extends NdcType
{
    public $id = '';
    public $name = '';
    public $is_city_dependent = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['is_city_dependent'], 'integer'],
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

        $this->id && $query->andWhere([$ndcTypeTableName . '.id' => $this->id]);
        $this->name && $query->andWhere(['LIKE', $ndcTypeTableName . '.name', $this->name]);
        $this->is_city_dependent !== '' && $query->andWhere(['is_city_dependent' => $this->is_city_dependent]);

        return $dataProvider;
    }
}
