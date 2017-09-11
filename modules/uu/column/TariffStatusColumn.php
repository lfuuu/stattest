<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\modules\uu\models\TariffStatus;
use kartik\grid\GridView;


class TariffStatusColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $serviceTypeId = null;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = TariffStatus::getList($this->serviceTypeId, $this->isWithEmpty);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' tariff-status-column';
    }
}