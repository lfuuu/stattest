<?php

namespace app\classes\grid\column\important_events;

use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\important_events\ImportantEventsSources;
use yii\helpers\ArrayHelper;

class SourceColumn extends DataColumn
{

    public
        $label = 'Источник',
        $attribute = 'source_id',
        $value = 'source_id',
        $filterType = '\app\widgets\multiselect\MultiSelect',
        $filterInputOptions = [];

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $sourcesList = ImportantEventsSources::find()->indexBy('code')->all();

        $this->filterWidgetOptions['data'] = $sourcesList;
        $this->filterWidgetOptions['nonSelectedText'] = '- Выберите источник(и) -';
        $this->filterWidgetOptions['clientOptions']['buttonWidth'] = '100%';

        $this->filterInputOptions['multiple'] = 'multiple';

        parent::__construct($config);
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return ($model->source->title ?: ($model->source->code ?: parent::getDataCellValue($model, $key, $index)));
    }

}