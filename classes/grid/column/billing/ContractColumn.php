<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\ClientContract;
use kartik\grid\GridView;

class ContractColumn extends DataColumn
{
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $filterByServerIds = null;
    public $filterByServiceTrunkIds = null;
    public $filterByTrunkIds = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ClientContract::dao()->getListWithType(
            [
                'serverIds' => $this->filterByServerIds,
                'serviceTrunkIds' => $this->filterByServiceTrunkIds,
                'trunkIds' => $this->filterByTrunkIds,
            ]
        );
    }

}