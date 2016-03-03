<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use kartik\datetime\DateTimePicker;
use Yii;


class DateTimeRangeDoubleColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter =
            DateTimePicker::widget(
                [
                    'model' => $this->grid->filterModel,
                    'attribute' => $this->attribute . '_from',
                    'removeButton' => false,
                    'type' => DateTimePicker::TYPE_INPUT,
                    'options' => [
                        'class' => 'form-control input-sm input-datetime',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd hh:ii:00',
                    ],
                ]
            ) .
            ' ' .
            DateTimePicker::widget(
                [
                    'model' => $this->grid->filterModel,
                    'attribute' => $this->attribute . '_to',
                    'removeButton' => false,
                    'type' => DateTimePicker::TYPE_INPUT,
                    'options' => [
                        'class' => 'form-control input-sm input-datetime',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd hh:ii:00',
                    ],
                ]
            );
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' datetime-range-double-column';
    }
}