<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\Region;
use kartik\grid\GridView;
use Yii;


class ConnectionPointColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Region::dao()->getList(true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' connection-point-column';
    }
}