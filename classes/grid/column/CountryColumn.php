<?php

namespace app\classes\grid\column;

use app\models\Country;
use kartik\grid\GridView;
use Yii;


class CountryColumn extends DataColumn
{
    public $attribute = 'country_id';
    public $value = 'country.name';
    public $label = 'Страна';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = Country::dao()->getList(true);
        parent::__construct($config);
    }
}