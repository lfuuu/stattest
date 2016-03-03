<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\billing\Destination;
use kartik\grid\GridView;

class DestinationColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = Destination::getList(true);
        parent::__construct($config);
        $this->filterInputOptions['class'] .= ' destination-column';
    }
}