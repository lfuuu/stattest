<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\Html;
use Yii;


class IntegerRangeColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public $step = 1;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter =
            Html::activeInput('number', $this->grid->filterModel, $this->attribute . '_from', [
                'class' => 'form-control input-sm',
                'step' => $this->step,
            ]) .

            ' ' .

            Html::activeInput('number', $this->grid->filterModel, $this->attribute . '_to', [
                'class' => 'form-control input-sm',
                'step' => $this->step,
            ]);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' integer-range-column';
    }
}