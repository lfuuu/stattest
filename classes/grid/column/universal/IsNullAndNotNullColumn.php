<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\traits\GetListTrait;
use kartik\grid\GridView;


class IsNullAndNotNullColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = GetListTrait::getEmptyList($isWithEmpty = true, $isWithNullAndNotNull = true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' is-null-and-not-null-column';
    }
}