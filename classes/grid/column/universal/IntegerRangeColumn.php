<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\Html;
use Yii;


class IntegerRangeColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $filterModel = $this->grid->filterModel;
        $this->filter =
            Html::activeInput('number', $filterModel, $this->attribute . '_from', [
                'class' => 'form-control input-sm input-tinyint',
            ]) .

            ' ' .

            Html::activeInput('number', $filterModel, $this->attribute . '_to', [
                'class' => 'form-control input-sm input-tinyint',
            ]);
    }
}