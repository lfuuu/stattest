<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use kartik\grid\GridView;

/**
 * Class DropdownColumn
 *
 * Универсальный класс для подключения своих колонок с dropdown-фильтром.
 */
class DropdownColumn extends DataColumn
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
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' dropdown-column';
        $this->isWriteNotSet = true;
    }
}