<?php

namespace app\classes\grid\column\user;

use kartik\grid\GridView;

class UserEnabledColumn extends \kartik\grid\BooleanColumn
{

    public $value = 'enabled';

    public function getDataCellValue($model, $key, $index)
    {
        return
            $model->{$this->value} == 'yes'
                ? GridView::ICON_ACTIVE
                : GridView::ICON_INACTIVE;
    }

}