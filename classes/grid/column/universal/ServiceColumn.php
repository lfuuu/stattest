<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\mtt_raw\MttRaw;
use kartik\grid\GridView;


class ServiceColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = MttRaw::getServiceList($this->isWithEmpty);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' mtt-service-column';
    }
}