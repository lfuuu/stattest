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
        $value = parent::getDataCellValue($model, $key, $index);
        return \Yii::$app->user->can('users.change') ? Html::a($value, ['edit', 'id' => $model->{$this->attribute}]) : $value;
    }
}