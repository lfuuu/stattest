<?php

namespace app\modules\nnp2\filters;

use app\modules\nnp2\models\NdcType;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для NdcType
 */
class NdcTypeFilter extends NdcType
{
    public $id = '';
    public $name = '';
    public $is_city_dependent = '';
    public $parent_id = '';
    public $is_valid = '';

    public $sort;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['is_city_dependent', 'parent_id', 'is_valid'], 'integer'],
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

        $this->parent_id && $query->andWhere([$ndcTypeTableName . '.parent_id' => $this->parent_id]);
        if (
            ($this->is_valid !== '')
            && !is_null($this->is_valid)
        ) {
            $query->andWhere([$ndcTypeTableName . '.is_valid' => (bool)$this->is_valid]);
        }

        if (!$this->sort) {
            $query->addOrderBy(['id' => SORT_ASC]);
        }

        return $dataProvider;
    }
}
