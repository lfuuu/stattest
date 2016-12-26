<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use kartik\grid\GridView;

/**
 * Class ConstructColumn
 * @package app\classes\grid\column\universal
 */
class ConstructColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;
    public $filter = [];

    /**
     * ConstructColumn constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        if ($this->isWithEmpty) {
            $this->filter = ['' => '----'] + $this->filter;
        }
    }
}
