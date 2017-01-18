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
    public $country_code = '';
    public $cnt_from = '';
    public $cnt_to = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['country_code', 'cnt_from', 'cnt_to'], 'integer'],
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
        $this->country_code && $query->andWhere([$operatorTableName . '.country_code' => $this->country_code]);

        $this->cnt_from !== '' && $query->andWhere(['>=', $operatorTableName . '.cnt', $this->cnt_from]);
        $this->cnt_to !== '' && $query->andWhere(['<=', $operatorTableName . '.cnt', $this->cnt_to]);

        return $dataProvider;
    }
}
