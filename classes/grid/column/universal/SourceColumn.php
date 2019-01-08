<?php

namespace app\classes\grid\column\universal;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\grid\column\DataColumn;
use kartik\grid\GridView;


class SourceColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ['' => '----'] + VoipRegistrySourceEnum::$names;
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' source-column';
    }
}