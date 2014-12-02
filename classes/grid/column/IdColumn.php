<?php

namespace app\classes\grid\column;

use Yii;
use yii\helpers\Html;


class IdColumn extends DataColumn
{
    public $attribute = 'id';
    public $label = '#';
    public $width = '50px';

    protected function renderDataCellContent($model, $key, $index)
    {
        return Html::a(parent::getDataCellValue($model, $key, $index), ['edit', 'id' => $model->id]);
    }
}