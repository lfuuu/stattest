<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\billing\CallsRawUnite;
use kartik\grid\GridView;

class TrafficTypeColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = CallsRawUnite::$trafficTypes;

        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' traffic-type-column';
    }
}