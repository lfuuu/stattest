<?php

namespace app\classes\grid\column;

use Yii;
use yii\helpers\Html;


class NameColumn extends DataColumn
{
    public $attribute = 'name';
    public $label = 'Название';

    protected function renderDataCellContent($model, $key, $index)
    {
        return Html::a(parent::getDataCellValue($model, $key, $index), ['edit', 'id' => $model->id]);
    }
}