<?php

namespace app\classes\grid\column;

use app\models\Region;
use kartik\grid\GridView;


class RegionColumn extends DataColumn
{
    public $attribute = 'region_id';
    public $value = 'region.name';
    public $label = 'Регион';
    public $filterType = GridView::FILTER_SELECT2;
    public $typeId = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Region::getList($isWithEmpty = true, $countryId = null, $this->typeId);
    }
}