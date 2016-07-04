<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\uu\model\Tariff;
use kartik\grid\GridView;
use Yii;


class TariffColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $serviceTypeId = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Tariff::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $this->serviceTypeId);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' tariff-column';
    }
}