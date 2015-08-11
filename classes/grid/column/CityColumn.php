<?php

namespace app\classes\grid\column;

use app\models\City;
use kartik\grid\GridView;
use Yii;


class CityColumn extends DataColumn
{
    public $attribute = 'city_id';
    public $value = 'city.name';
    public $label = 'Город';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = City::dao()->getListWithCountries(true);
        parent::__construct($config);
    }
}