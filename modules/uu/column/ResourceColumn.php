<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\modules\uu\models\ResourceClass;
use kartik\grid\GridView;


class ResourceColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
//    use ListTrait; // @todo

    public $filterType = GridView::FILTER_SELECT2;
    public $serviceTypeId = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ResourceClass::getList($this->serviceTypeId, true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' resource-column';
    }
}