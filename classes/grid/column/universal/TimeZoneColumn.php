<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\helpers\DateTimeZoneHelper;
use app\models\Region;
use kartik\grid\GridView;


class TimeZoneColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmptyText = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Region::getTimezoneList();
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' region-column';
    }
}