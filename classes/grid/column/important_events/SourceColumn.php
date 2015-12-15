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
    public $filterType = '\app\widgets\select_multiply\SelectMultiply';
    public $filterInputOptions = null;

    public function __construct($config = [])
    {
        $sourcesList =  ArrayHelper::map(ImportantEventsSources::find()->all(), 'id', 'title');

        $this->filterWidgetOptions['items'] = $sourcesList;
        $this->filterWidgetOptions['clientOptions']['placeholder'] = '- Выберите источник(и) -';
        $this->filterWidgetOptions['clientOptions']['width'] = '100%';

        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        return $model->source->title ?: parent::getDataCellValue($model, $key, $index);
    }

}