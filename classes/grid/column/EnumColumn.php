<?php

namespace app\classes\grid\column;

use Yii;
use yii\helpers\Html;


class EnumColumn extends DataColumn
{
    public $enum;

    protected function renderDataCellContent($model, $key, $index)
    {
        $enumClass = $this->enum;
        return $enumClass::getName(parent::getDataCellValue($model, $key, $index));
    }
}