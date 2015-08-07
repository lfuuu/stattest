<?php

namespace app\classes\grid\column;

use app\models\DidGroup;
use Yii;


class BeautyLevelColumn extends DataColumn
{
    public $attribute = 'beauty_level';
    public $label = 'Красивость';

    protected function renderDataCellContent($model, $key, $index)
    {
        return DidGroup::$beautyLevelNames[parent::getDataCellValue($model, $key, $index)];
    }
}