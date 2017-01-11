<?php

namespace app\classes\grid\column\billing;

use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\billing\ServiceTrunk;

class ServiceTrunkColumn extends DataColumn
{
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $filterByServerId = '';
    public $filterByTrunkName = '';

    public function __construct($config = [])
    {
        parent::__construct($config);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' trunk-column';

        $this->filter = ServiceTrunk::getListWithName(
            $this->filterByServerId,
            $this->filterByTrunkName
        );
    }

}
