<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use kartik\date\DatePicker;


class DateRangeDoubleColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter =
            DatePicker::widget(
                [
                    'model' => $this->grid->filterModel,
                    'attribute' => $this->attribute . '_from',
                    'removeButton' => false,
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => [
                        'class' => 'form-control input-sm input-date',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ]
            ) .
            ' ' .
            DatePicker::widget(
                [
                    'model' => $this->grid->filterModel,
                    'attribute' => $this->attribute . '_to',
                    'removeButton' => false,
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => [
                        'class' => 'form-control input-sm input-date',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ]
            );
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' date-range-double-column';
    }
}