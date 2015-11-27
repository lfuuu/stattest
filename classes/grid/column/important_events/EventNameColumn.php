<?php

namespace app\classes\grid\column\important_events;

use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\ImportantEventsNames;
use yii\helpers\ArrayHelper;

class EventNameColumn extends DataColumn
{

    public $label = 'Событие';
    public $attribute = 'event';
    public $value = 'event';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = array_merge(
            ['' => '- Выберите событие -'],
            ArrayHelper::map(ImportantEventsNames::find()->all(), 'code', 'value')
        );
        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        return $model->name->value ?: parent::getDataCellValue($model, $key, $index);
    }
}