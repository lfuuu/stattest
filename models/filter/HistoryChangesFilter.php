<?php

namespace app\models\filter;

use app\models\HistoryChanges;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для HistoryChanges
 */
class HistoryChangesFilter extends HistoryChanges
{
    public $model = '';
    public $model_id = '';
    public $parent_model_id = '';
    public $user_id = '';
    public $created_at = '';
    public $action = '';

    public $created_at_from = '';
    public $created_at_to = '';

    public function rules()
    {
        return [
            [['model', 'action'], 'string'],
            [['model_id', 'parent_model_id', 'user_id'], 'integer'],
            [['created_at_from', 'created_at_to'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = HistoryChanges::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $this->model !== '' && $query->andWhere(['model' => $this->model]);
        $this->model_id !== '' && $query->andWhere(['model_id' => $this->model_id]);
        $this->parent_model_id !== '' && $query->andWhere(['parent_model_id' => $this->parent_model_id]);
        $this->user_id !== '' && $query->andWhere(['user_id' => $this->user_id]);
        $this->action !== '' && $query->andWhere(['action' => $this->action]);

        $this->created_at_from !== '' && $query->andWhere(['>=', 'created_at', $this->created_at_from]);
        $this->created_at_to !== '' && $query->andWhere(['<=', 'created_at', $this->created_at_to]);

        return $dataProvider;
    }
}
