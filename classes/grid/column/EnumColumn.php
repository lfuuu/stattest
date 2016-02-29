<?php

namespace app\classes\grid\column;

use Yii;
use kartik\grid\GridView;


class EnumColumn extends DataColumn
{
    public $enum;
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $enumClass = $this->enum;
        $this->filter = ['' => ' -------- '] + $enumClass::getNames();

    }

    protected function renderDataCellContent($model, $key, $index)
    {
        $enumClass = $this->enum;
        return $enumClass::getName(parent::getDataCellValue($model, $key, $index));
    }
}