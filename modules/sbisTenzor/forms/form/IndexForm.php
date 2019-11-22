<?php

namespace app\modules\sbisTenzor\forms\form;

use app\modules\sbisTenzor\models\SBISExchangeForm;
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
     * Получить запрос на выборку списка форм первичных документов
     *
     * @return ActiveDataProvider
     */
    public function getDataProvider()
    {
        $query = SBISExchangeForm::find();

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
        return 'Формы первичных документов';
    }
}