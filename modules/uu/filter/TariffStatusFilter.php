<?php

namespace app\modules\uu\filter;

use app\modules\uu\models\TariffStatus;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для TariffStatus
 */
class TariffStatusFilter extends TariffStatus
{
    public $name = '';
    public $service_type_id = '';

    /**
     * TariffStatusFilter constructor.
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
        $query = TariffStatus::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name !== '' && $query->andWhere(['like', 'name', $this->name]);
        $this->service_type_id !== '' && $query->andWhere(['service_type_id' => $this->service_type_id]);

        return $dataProvider;
    }
}
