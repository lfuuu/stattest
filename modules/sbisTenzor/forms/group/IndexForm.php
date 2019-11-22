<?php

namespace app\modules\sbisTenzor\forms\group;

use app\modules\sbisTenzor\models\SBISExchangeGroup;
use yii\data\ActiveDataProvider;

class IndexForm extends \app\classes\Form
{
    /**
     * Index form constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Получить запрос на выборку групп обмена первичными документами
     *
     * @return ActiveDataProvider
     */
    public function getDataProvider()
    {
        $query = SBISExchangeGroup::find();

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Группы обмена первичными документами';
    }
}