<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\uu\model\ServiceType;
use kartik\grid\GridView;
use Yii;


class ServiceTypeColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ServiceType::getList(true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' service-type-column';
    }
}