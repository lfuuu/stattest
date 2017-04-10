<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\widgets\MonthPicker;


class MonthColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = MonthPicker::widget([
            'model' => $this->grid->filterModel,
            'attribute' => $this->attribute,
            'options' => [
                'class' => 'form-control input-sm month-column',
            ],
        ]);
    }
}