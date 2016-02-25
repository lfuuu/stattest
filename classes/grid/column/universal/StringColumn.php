<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\Html;
use Yii;


class StringColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $filterModel = $this->grid->filterModel;
        $this->filter =
            Html::activeTextInput($filterModel, $this->attribute, [
                'class' => 'form-control input-sm',
            ]);
    }
}