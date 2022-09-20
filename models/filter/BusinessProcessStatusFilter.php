<?php

namespace app\models\filter;

use app\models\BusinessProcessStatus;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для BusinessProcessStatus
 */
class BusinessProcessStatusFilter extends BusinessProcessStatus
{
    public $id = '';
    public $name = '';
    public $business_process_id = '';
    public $is_bill_send = '';
    public $is_off_stage = '';
    public $is_with_wizard = '';

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = BusinessProcessStatus::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        $this->name !== '' && $query->andWhere(['LIKE', 'name', $this->name]);
        $this->business_process_id !== '' && $query->andWhere(['business_process_id' => $this->business_process_id]);
        $this->is_bill_send !== '' && $query->andWhere(['is_bill_send' => (bool)(int)$this->is_bill_send]);
        $this->is_off_stage !== '' && $query->andWhere(['is_off_stage' => (bool)(int)$this->is_off_stage]);
        $this->is_with_wizard !== '' && $query->andWhere(['is_with_wizard' => (bool)(int)$this->is_with_wizard]);

        $query->orderBy([
            'business_process_id' => SORT_ASC,
            'sort' => SORT_ASC
        ]);

        return $dataProvider;
    }
}
