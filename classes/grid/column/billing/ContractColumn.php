<?php

namespace app\classes\grid\column\billing;

use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\dao\ClientContractDao;

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
        $this->filter = ClientContractDao::getListWithType(
            [
                'serverIds' => $this->filterByServerIds,
                'serviceTrunkIds' => $this->filterByServiceTrunkIds,
                'trunkIds' => $this->filterByTrunkIds,
            ]
        );
    }

}