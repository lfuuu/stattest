<?php

namespace app\classes\grid\column\group;

use Yii;
use app\classes\grid\column\DataColumn;
use yii\helpers\Html;

class GroupNameColumn extends DataColumn
{
    public $attribute = 'usergroup';
    public $value = 'usergroup';

    protected function renderDataCellContent($model, $key, $index)
    {
        return Html::a(parent::getDataCellValue($model, $key, $index), ['edit', 'id' => $model->{$this->attribute}]);
    }
}