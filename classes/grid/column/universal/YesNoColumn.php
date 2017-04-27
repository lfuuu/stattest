<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\traits\YesNoTraits;
use kartik\grid\GridView;


class YesNoColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $yesLabel = null;
    public $noLabel = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = YesNoTraits::getYesNoList(true);
        if (!is_null($this->noLabel)) {
            $this->filter[0] = $this->noLabel;
        }
        if (!is_null($this->yesLabel)) {
            $this->filter[1] = $this->yesLabel;
        }
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' yes-no-column';
    }
}