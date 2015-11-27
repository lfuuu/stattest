<?php

namespace app\classes\grid\column\important_events;

use app\classes\Html;
use app\classes\grid\column\DataColumn;

class PropertiesColumn extends DataColumn
{

    public $label = 'Данные';
    public $attribute = 'extends_data';

    protected function renderDataCellContent($model, $key, $index)
    {
        $result = [];
        foreach ($model->properties as $property) {
            $result[] = Html::tag('b', $property->property) . ': ' . $property->value;
        }

        return implode(', ', $result);
    }
}