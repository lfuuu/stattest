<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\billing\Trunk;
use kartik\grid\GridView;

class TrunkColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $filterByIds = [];
    public $filterByServerIds = null;
    public $filterByServiceTrunkIds = null;
    public $filterByContractIds = null;
    public $isWithEmpty = true;
    public $filterByShowInStat = true;
    public $filterByTrunkGroupIds = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' trunk-column';

        $this->filter = Trunk::dao()->getList(
            [
                'serverIds' => $this->filterByServerIds,
                'trunkGroupIds' => $this->filterByTrunkGroupIds,
                'serviceTrunkIds' => $this->filterByServiceTrunkIds,
                'contractIds' => $this->filterByContractIds,
                'showInStat' => $this->filterByShowInStat,
            ],
            $this->isWithEmpty
        );

        // если выбран оператор, то в списке показать только его транки
        if ($this->filterByIds) {
            $filterByIds = $this->filterByIds;
            $this->filter = array_filter(
                $this->filter,
                function ($trunkId) use ($filterByIds) {
                    return $trunkId === '' || isset($filterByIds[$trunkId]);
                },
                ARRAY_FILTER_USE_KEY
            );
        }
    }

}