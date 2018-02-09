<?php

namespace app\modules\uu\filter;

use app\modules\uu\models\TariffVm;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для TariffVm
 */
class TariffVmFilter extends TariffVm
{
    public $id = '';
    public $name = '';

    /**
     * TariffVmFilter constructor.
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
        $query = TariffVm::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(['id' => $this->id]);
        $this->name !== '' && $query->andWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
