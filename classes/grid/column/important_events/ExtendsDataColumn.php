<?php

namespace app\classes\grid\column\important_events;

use app\classes\Html;
use app\classes\grid\column\DataColumn;

class ExtendsDataColumn extends DataColumn
{

    public $label = 'Данные';
    public $attribute = 'extends_data';

    protected function renderDataCellContent($model, $key, $index)
    {
        $extendsData = 'Ошибка разбора JSON';
        try {
            $extendsData = json_decode(parent::getDataCellValue($model, $key, $index));
        }
        catch (\Exception $e) {}

        $result = [];
        foreach ($extendsData as $key => $value) {
            $result[] = Html::tag('b', $key) . ': ' . $value;
        }

        return implode(', ', $result);
    }
}