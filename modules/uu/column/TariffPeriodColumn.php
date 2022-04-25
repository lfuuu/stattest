<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\modules\uu\models\TariffPeriod;
use kartik\grid\GridView;


class TariffPeriodColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
//    use ListTrait; @todo

    public $filterType = GridView::FILTER_SELECT2;

    public $serviceTypeId = null;

    public $withTariffId = false;

    public $currency = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = TariffPeriod::getList(
            $defaultTariffPeriodId,
            $this->serviceTypeId,
            $this->currency,
            $countryIdTmp = null,
            $voipCountryIdTmp = null,
            $cityIdTmp = null,
            $isWithEmptyTmp = true,
            $isWithNullAndNotNullTmp = true,
            $statusId = null,
            $isIncludeVat = null,
            $organizationId = null,
            $ndcTypeId = null,
            $withTariffId = $this->withTariffId);

        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' tariff-period-column';
    }
}