<?php

namespace app\classes\grid\column;

use app\models\Region;
use kartik\grid\GridView;
use Yii;


class RegionColumn extends DataColumn
{
    public $attribute = 'region_id';
    public $value = 'region.name';
    public $label = 'Регион';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = Region::dao()->getList(true);
        parent::__construct($config);
    }
}