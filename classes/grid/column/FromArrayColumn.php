<?php

namespace app\classes\grid\column;

class FromArrayColumn extends DataColumn
{

    public $variants = [];

    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        return isset($this->variants[$value]) ? $this->variants[$value] : $value;
    }

}