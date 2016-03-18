<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\Html;
use Yii;


class IntegerColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter =
            Html::activeInput('number', $this->grid->filterModel, $this->attribute, [
                'class' => 'form-control input-sm',
            ]);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' integer-column';
    }
}