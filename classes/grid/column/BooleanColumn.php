<?php

namespace app\classes\grid\column;

class BooleanColumn extends \kartik\grid\BooleanColumn
{

    public $values;

    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        $value = ($value == $this->trueIcon) ? 1 : 0;

        return
            isset($this->values[$value])
                ? $this->values[$value]
                : ($key ? $model->$key : $key);
    }

}

