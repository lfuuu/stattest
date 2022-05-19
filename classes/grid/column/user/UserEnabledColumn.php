<?php

namespace app\classes\grid\column\user;

use app\classes\grid\column\DataColumn;
use kartik\grid\GridView;

class UserEnabledColumn extends DataColumn
{
    public $attribute = 'enabled';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => '---', 'no' => 'Выключен', 'yes' => 'Активен'];
        parent::__construct($config);
    }

    public function renderDataCellContent($model, $key, $index)
    {
        return
            $model->{$this->attribute} == 'yes'
                ? GridView::ICON_ACTIVE
                : GridView::ICON_INACTIVE;
    }

}