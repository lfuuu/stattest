<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\Business;
use app\models\BusinessProcess;
use kartik\grid\GridView;

class TrunkBusinessColumn extends DataColumn
{

    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $filter = ['' => '----'];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter += BusinessProcess::getList($isWithEmpty = false, $isWithNullAndNotNull = false, $businessId = Business::OPERATOR);
    }

}