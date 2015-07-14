<?php

namespace app\classes\grid\column;

use kartik\grid\GridView;
use app\models\TariffVoip;

class DestinationColumn extends DataColumn
{

    public $label = 'Направление';

    public $values = [
        '1' => 'Россия',
        '2' => 'Международка',
        '4' => 'Местные Стационарные',
        '5' => 'Местные Мобильные',
    ];

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => ' -------- '] + TariffVoip::$destinations;
        parent::__construct($config);
    }

    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        return isset($this->values[$value]) ? $this->values[$value] : $value;
    }

}
