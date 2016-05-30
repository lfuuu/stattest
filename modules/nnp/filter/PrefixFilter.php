<?php

namespace app\modules\nnp\filter;

use app\modules\nnp\models\Prefix;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Prefix
 */
class PrefixFilter extends Prefix
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
        $query = Prefix::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', 'name', $this->name]);

        return $dataProvider;
    }
}
