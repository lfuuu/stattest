<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use kartik\date\DatePicker;
use ReflectionClass;
use Yii;


class DateRangeColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $filterModel = $this->grid->filterModel;
        $filterModelName = (new ReflectionClass($filterModel))->getShortName();
        $this->filter =
            DatePicker::widget(
                [
                    'name' => sprintf('%s[%s_%s]', $filterModelName, $this->attribute, 'from'),
                    'value' => $filterModel->{$this->attribute . '_from'},
                    'removeButton' => false,
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['class' => 'form-control input-sm input-date'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ]
            ) .
            ' ' .
            DatePicker::widget(
                [
                    'name' => sprintf('%s[%s_%s]', $filterModelName, $this->attribute, 'to'),
                    'value' => $filterModel->{$this->attribute . '_to'},
                    'removeButton' => false,
                    'type' => DatePicker::TYPE_INPUT,
                    'options' => ['class' => 'form-control input-sm input-date'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ],
                ]
            );
    }
}