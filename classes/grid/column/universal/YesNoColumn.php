<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\traits\YesNoTraits;
use kartik\grid\GridView;
use Yii;


class YesNoColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = YesNoTraits::getYesNoList(true);
        $this->filterInputOptions['class'] .= ' yes-no-column';
    }
}