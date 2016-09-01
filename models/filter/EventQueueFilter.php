<?php

namespace app\models\filter;

use app\models\EventQueue;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для EventQueue
 */
class EventQueueFilter extends EventQueue
{
    public $id = '';

    public $date_from = '';
    public $date_to = '';

    public $event = '';
    public $status = '';

    public $iteration_from = '';
    public $iteration_to = '';

    public $next_start_from = '';
    public $next_start_to = '';

    public $log_error = '';
    public $param = '';

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['date_from', 'date_to'], 'string'],
            [['next_start_from', 'next_start_to'], 'string'],
            [['event', 'status'], 'string'],
            [['iteration_from', 'iteration_to'], 'integer'],
            [['log_error', 'param'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = EventQueue::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        
        $this->date_from !== '' && $query->andWhere(['>=', 'date', $this->date_from]);
        $this->date_to !== '' && $query->andWhere(['<=', 'date', $this->date_to]);
        
        $this->next_start_from !== '' && $query->andWhere(['>=', 'next_start', $this->next_start_from]);
        $this->next_start_to !== '' && $query->andWhere(['<=', 'next_start', $this->next_start_to]);
        
        $this->event !== '' && $query->andWhere(['event' => $this->event]);
        $this->status !== '' && $query->andWhere(['status' => $this->status]);
        
        $this->iteration_from !== '' && $query->andWhere(['>=', 'iteration', $this->iteration_from]);
        $this->iteration_to !== '' && $query->andWhere(['<=', 'iteration', $this->iteration_to]);

        $this->log_error !== '' && $query->andWhere(['LIKE', 'log_error', $this->log_error]);
        $this->param !== '' && $query->andWhere(['LIKE', 'param', $this->param]);

        return $dataProvider;
    }
}
