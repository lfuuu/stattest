<?php

namespace app\modules\uu\filter;

use app\modules\uu\models\ServiceType;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для ServiceType
 */
class ServiceFolderFilter extends ServiceType
{
    public $id = null;

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

        $query->where(['parent_id' => null]);
        $query->andFilterWhere(['id' => $this->id]);

        return $dataProvider;
    }
}
