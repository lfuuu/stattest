<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\modules\uu\models\TariffVoipGroup;
use kartik\grid\GridView;


class TariffVoipGroupColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = TariffVoipGroup::getList(true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' tariff-voip-group-column';
    }
}