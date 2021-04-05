<?php

namespace app\models\filter;

use app\models\PriceLevel;
use yii\data\ActiveDataProvider;

class PriceLevelFilter extends PriceLevel
{
    public $id = '';
    public $name = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [ 
            [['id'], 'integer'],
            [['name'], 'string'],
        ];

    }

    /**
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = PriceLevel::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        $this->name !== '' && $query->andWhere(['LIKE', 'name', $this->name]);

        $query->orderBy(['order' => SORT_ASC]);

        return $dataProvider;
    }
}
