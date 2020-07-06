<?php

namespace app\modules\nnp2\filters;

use app\modules\nnp2\models\Operator;
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
    public $group = '';
    public $parent_id = '';
    public $is_valid = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit'], 'string'],
            [['id', 'country_code', 'cnt_from', 'cnt_to', 'group', 'parent_id', 'is_valid'], 'integer'],
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
        $this->id && $query->andWhere([$operatorTableName . '.id' => $this->id]);
        $this->country_code && $query->andWhere([$operatorTableName . '.country_code' => $this->country_code]);

        $this->cnt_from !== '' && $query->andWhere(['>=', $operatorTableName . '.cnt', $this->cnt_from]);
        $this->cnt_to !== '' && $query->andWhere(['<=', $operatorTableName . '.cnt', $this->cnt_to]);

        $this->group !== '' && $query->andWhere(["{$operatorTableName}.group" => $this->group]);

        $this->parent_id && $query->andWhere([$operatorTableName . '.parent_id' => $this->parent_id]);
        if (
            ($this->is_valid !== '')
            && !is_null($this->is_valid)
        ) {
            $query->andWhere([$operatorTableName . '.is_valid' => (bool)$this->is_valid]);
        }

        $sort = \Yii::$app->request->get('sort');
        if (!$sort) {
            $query->addOrderBy(['id' => SORT_ASC]);
        }

        return $dataProvider;
    }
}
