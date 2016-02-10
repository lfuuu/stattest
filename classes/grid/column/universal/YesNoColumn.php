<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\traits\YesNoTraits;
use Yii;
use yii\bootstrap\Html;


class YesNoColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = Html::activeDropDownList(
            $this->grid->filterModel,
            $this->attribute,
            YesNoTraits::getYesNoList(true),
            ['class' => 'form-control input-sm input-tinyint']
        );
    }
}