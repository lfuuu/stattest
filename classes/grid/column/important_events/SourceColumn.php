<?php

namespace app\classes\grid\column\important_events;

use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\important_events\ImportantEventsSources;
use yii\helpers\ArrayHelper;

class SourceColumn extends DataColumn
{

    public $label = 'Источник';
    public $attribute = 'source_id';
    public $value = 'source_id';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => '- Выберите источник -'] + ArrayHelper::map(ImportantEventsSources::find()->all(), 'id', 'title');
        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        return $model->source->title ?: parent::getDataCellValue($model, $key, $index);
    }
}