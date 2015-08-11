<?php

namespace app\classes\grid\column;

use app\models\Region;
use kartik\grid\GridView;

class ConnectionPointColumn extends DataColumn
{

    public $attribute = 'connection_point_id';
    public $value = 'connectionPoint.name';
    public $label = 'Точка подключения';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => ' -------- '] + Region::dao()->getList(false);
        parent::__construct($config);
    }

}