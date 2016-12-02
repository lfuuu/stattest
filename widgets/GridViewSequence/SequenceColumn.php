<?php

namespace app\widgets\GridViewSequence;

use app\classes\grid\column\DataColumn;
use yii\helpers\Html;

class SequenceColumn extends DataColumn
{

    public $headerOptions = ['style' => 'width: 30px;'];

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return Html::tag('div', '&#9776;', [
            'class' => 'sortable-widget-handler',
            'data-id' => $model->getPrimaryKey(),
        ]);
    }

}