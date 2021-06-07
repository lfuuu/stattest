<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\PriceLevel;
use kartik\grid\GridView;


class PriceLevelColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    // public $filterType = GridView::FILTER_SELECT3;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = PriceLevel::getList($this->isWithEmpty);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' price-level-column';
    }
}