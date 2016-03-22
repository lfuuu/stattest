<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\uu\model\Resource;
use kartik\grid\GridView;
use Yii;


class ResourceColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
//    use ListTrait; // @todo

    public $filterType = GridView::FILTER_SELECT2;
    public $serviceTypeId = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Resource::getList($this->serviceTypeId, true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' resource-column';
    }
}