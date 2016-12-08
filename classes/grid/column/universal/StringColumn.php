<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\Html;
use Yii;


class StringColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';
    public $options = [];

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter =
            Html::activeTextInput($this->grid->filterModel, $this->attribute, array_merge([
                'class' => 'form-control input-sm',
            ], $this->options));
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' string-column';
    }
}