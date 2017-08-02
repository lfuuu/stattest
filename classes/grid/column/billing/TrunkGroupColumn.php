<?php

namespace app\classes\grid\column\billing;

use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\billing\TrunkGroup;

class TrunkGroupColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;
    public $filterByServerIds = null;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = TrunkGroup::getList($this->filterByServerIds, $this->isWithEmpty);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' trunk-group-column';
    }

}