<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\universal\DropdownColumn;

class DropdownColumnMulti extends DropdownColumn
{
    public $filterWidgetOptions = [
        'pluginOptions' => [
            'allowClear' => true,
            'multiple' => true,
        ],
    ];
}
