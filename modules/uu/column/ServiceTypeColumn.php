<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\modules\uu\models\ServiceType;
use kartik\grid\GridView;


class ServiceTypeColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $isOnlyTopLevelStatuses = false;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ServiceType::getList(true, false, $this->isOnlyTopLevelStatuses);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' service-type-column';
    }
}