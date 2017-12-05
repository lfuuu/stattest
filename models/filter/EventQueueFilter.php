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
    public $account_tariff_id = '';

    public $insert_time_from = '';
    public $insert_time_to = '';

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
            [['id', 'account_tariff_id'], 'integer'],
            [['insert_time_from', 'insert_time_to'], 'string'],
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
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        $this->account_tariff_id !== '' && $query->andWhere(['account_tariff_id' => $this->account_tariff_id]);

        $this->insert_time_from !== '' && $query->andWhere(['>=', 'insert_time', $this->insert_time_from]);
        $this->insert_time_to !== '' && $query->andWhere(['<=', 'insert_time', $this->insert_time_to]);

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
