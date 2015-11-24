<?php

namespace app\classes\grid\column\important_events;

use kartik\grid\GridView;
use app\classes\Html;
use app\classes\grid\column\DataColumn;
use app\models\ImportantEvents;

class EventNameColumn extends DataColumn
{

    public $label = 'Событие';
    public $attribute = 'event';
    public $value = 'event';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = array_merge(['' => '- Выберите событие -'], ImportantEvents::$eventsList);
        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        $eventKey = parent::getDataCellValue($model, $key, $index);
        return isset(ImportantEvents::$eventsList[$eventKey]) ? ImportantEvents::$eventsList[$eventKey] : $eventKey;
    }
}