<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use kartik\grid\GridView;

class ReleasingPartyColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        $this->filter = [
            0 => 'Incoming',
            1 => 'Outgoing',
        ];

        if ($this->isWithEmpty) {
            $this->filter = ['' => '----'] + $this->filter;
        }

        parent::__construct($config);
    }
}