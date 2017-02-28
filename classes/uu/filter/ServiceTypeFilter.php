<?php

namespace app\classes\uu\filter;

use app\classes\uu\model\ServiceType;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для ServiceType
 */
class ServiceTypeFilter extends ServiceType
{
    public $name = '';
    public $close_after_days = '';

    /**
     * ServiceTypeFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = ServiceType::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name !== '' && $query->andWhere(['like', 'name', $this->name]);
        $this->close_after_days !== '' && $query->andWhere(['close_after_days' => $this->close_after_days]);

        return $dataProvider;
    }
}
