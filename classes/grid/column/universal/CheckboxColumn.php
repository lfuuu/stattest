<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\Html;
use kartik\grid\GridView;


class CheckboxColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;

    public $filter = '';
    public $options = [];
    public $label = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter =
            Html::activeCheckbox($this->grid->filterModel, $this->attribute, array_merge($this->options) + ['label' => $this->label]);
    }
}