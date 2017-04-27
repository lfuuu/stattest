<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use kartik\grid\GridView;

/**
 * Class ConstructColumn
 */
class ConstructColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;
    public $filter = [];

    /**
     * ConstructColumn constructor.
     *
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
