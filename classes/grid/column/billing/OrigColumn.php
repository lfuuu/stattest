<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use kartik\grid\GridView;

class OrigColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = [
            '' => ' ---- ',
            0 => 'Терминация',
            1 => 'Оригинация',
        ];
        parent::__construct($config);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' orig-column';
    }
}