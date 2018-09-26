<?php

namespace app\modules\nnp\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\modules\nnp\models\Operator;
use kartik\grid\GridView;

class OperatorTypeColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $filterType = GridView::FILTER_SELECT2;
    public $isAddLink = true;
    public $isWithNullAndNotNull = false;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ['' => '- Все -'] + Operator::$groups;
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' group-column';
    }

}