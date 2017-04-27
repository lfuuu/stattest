<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;

class WithEmptyFilterColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';
}