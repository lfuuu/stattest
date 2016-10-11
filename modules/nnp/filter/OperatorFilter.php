<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\Operator;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Operator
 */
class OperatorFilter extends Operator
{
    public $name = '';
    public $country_prefix = '';

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['country_prefix'], 'integer'],
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
        $this->country_prefix && $query->andWhere([$operatorTableName . '.country_prefix' => $this->country_prefix]);

        return $dataProvider;
    }
}
