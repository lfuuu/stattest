<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\uu\model\TariffPeriod;
use kartik\grid\GridView;
use Yii;


class TariffPeriodColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
//    use ListTrait; @todo

    public $filterType = GridView::FILTER_SELECT2;
    public $serviceTypeId = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = TariffPeriod::getList($defaultTariffPeriodId, $this->serviceTypeId, $currencyTmp = null,
            $cityTmp = null, $isWithEmptyTmp = true, $isWithClosedTmp = true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' tariff-period-column';
    }
}