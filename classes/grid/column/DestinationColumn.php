<?php

namespace app\classes\grid\column;

use kartik\grid\GridView;
use app\models\TariffVoip;

class DestinationColumn extends DataColumn
{

    public $label = 'Направление';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => ' -------- '] + TariffVoip::$destinations;
        parent::__construct($config);
    }

    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        return isset(TariffVoip::$destinations[$value]) ?TariffVoip::$destinations[$value] : $value;
    }

}
