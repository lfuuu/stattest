<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use Yii;


class WithoutFilterColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    /**
     * Renders the filter cell.
     */
    public function renderFilterCell()
    {
        return '';
    }
}