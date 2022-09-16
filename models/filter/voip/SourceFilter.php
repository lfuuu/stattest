<?php

namespace app\models\filter\voip;

use app\models\voip\Source;
use yii\data\ActiveDataProvider;

class SourceFilter extends Source
{
    public $code = '';
    public $name = '';
    public $is_service = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [ 
            [['code', 'name'], 'string'],
            ['is_service', 'integer'],
        ];

    }

    /**
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Source::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->code !== '' && $query->andWhere(['code' => $this->code]);
        $this->name !== '' && $query->andWhere(['LIKE', 'name', $this->name]);
        $this->is_service !== '' && $query->andWhere(['is_service' => (int)$this->is_service]);

        $query->orderBy(['order' => SORT_ASC]);

        return $dataProvider;
    }
}
