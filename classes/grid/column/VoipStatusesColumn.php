<?php

namespace app\classes\grid\column;

use app\models\TariffVoip;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;

class VoipStatusesColumn extends DataColumn
{

    public $attribute = 'status';
    public $label = 'Статус';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => ' ---------- '] + TariffVoip::$statuses;
        parent::__construct($config);
    }

    public function getDataCellValue($model, $key, $index)
    {
        $values = TariffVoip::$statuses;
        $value = parent::getDataCellValue($model, $key, $index);
        return isset($values[$value]) ? $values[$value] : $value;
    }

}