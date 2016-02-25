<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\models\Region;
use Yii;
use app\classes\Html;


class RegionColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = Html::activeDropDownList(
            $this->grid->filterModel,
            $this->attribute,
            Region::getList(true),
            ['class' => 'form-control input-sm input-region']
        );
    }
}