<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\Operator;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Operator
 */
class OperatorFilter extends Operator
{
    public $id = '';
    public $name = '';
    public $name_translit = '';
    public $country_code = '';
    public $cnt_from = '';
    public $cnt_to = '';
    public $cnt_active_from = '';
    public $cnt_active_to = '';
    public $group = '';
    public $operator_src_code = '';
    public $parent_id = '';
    public $is_valid = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit', 'parent_id'], 'string'],
            [['id', 'country_code', 'cnt_from', 'cnt_to', 'cnt_active_from', 'cnt_active_to', 'group', 'operator_src_code', 'is_valid'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Operator::find();
        $operatorTableName = Operator::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', $operatorTableName . '.name', $this->name]);
        $this->name_translit && $query->andWhere(['LIKE', $operatorTableName . '.name_translit', $this->name_translit]);
        $this->operator_src_code && $query->andWhere(['LIKE', $operatorTableName . '.operator_src_code', $this->operator_src_code]);
        $this->id && $query->andWhere([$operatorTableName . '.id' => $this->id]);
        $this->country_code && $query->andWhere([$operatorTableName . '.country_code' => $this->country_code]);

        $this->cnt_from !== '' && $query->andWhere(['>=', $operatorTableName . '.cnt', $this->cnt_from]);
        $this->cnt_to !== '' && $query->andWhere(['<=', $operatorTableName . '.cnt', $this->cnt_to]);

        $this->cnt_active_from !== '' && $query->andWhere(['>=', $operatorTableName . '.cnt_active', $this->cnt_active_from]);
        $this->cnt_active_to !== '' && $query->andWhere(['<=', $operatorTableName . '.cnt_active', $this->cnt_active_to]);

        $this->group !== '' && $query->andWhere(["{$operatorTableName}.group" => $this->group]);
        $this->is_valid !== '' && $query->andWhere(["{$operatorTableName}.is_valid" => (bool)$this->is_valid]);

        if ($this->parent_id !== '') {
            $query->joinWith('parent p')->andWhere(['LIKE', 'p.name', $this->parent_id, true]);
        }

        $query->with('parent');

        return $dataProvider;
    }
}
