<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\models\Country;
use Yii;
use app\classes\Html;


class CountryColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $filterModel = $this->grid->filterModel;

        $this->filter = Html::activeDropDownList(
            $filterModel,
            $this->attribute,
            Country::getList(true),
            ['class' => 'form-control input-sm input-country']
        );
    }
}