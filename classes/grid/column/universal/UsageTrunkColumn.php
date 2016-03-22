<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\UsageTrunk;
use kartik\grid\GridView;

class UsageTrunkColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $trunkId = '';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = UsageTrunk::getList($this->trunkId, true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' usage-trunk-column';
    }
}