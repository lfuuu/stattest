<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\Bill;
use kartik\grid\GridView;

class PayedColumn extends DataColumn
{
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ['' => '----'] + Bill::$paidStatuses;
    }
}