<?php

use app\classes\grid\column\important_events\ClientColumn;
use app\classes\grid\column\important_events\EventNameColumn;
use app\classes\grid\column\important_events\SourceColumn;
use app\classes\grid\column\universal\TagsColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\important_events\ImportantEventsDetailsFactory;
use app\helpers\DateTimeZoneHelper;
use app\models\important_events\ImportantEvents;
use yii\data\ActiveDataProvider;

/** @var ActiveDataProvider $dataProvider */
/** @var ImportantEvents $filterModel */

echo Html::formLabel('Лог значимых событий');

foreach (\app\models\important_events\ImportantEventsNames::find()->all() as $event) {
    $eventsList[$event->group->title][$event->code] = $event->value;
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function () {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model) {
                return $this->render('details', ['model' => $model]);
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'attribute' => 'client_id',
            'class' => ClientColumn::class,
            'width' => '25%',
        ],
        'date' => [
            'attribute' => 'date',
            'width' => '15%',
            'format' => 'raw',
            'filter' => \kartik\daterange\DateRangePicker::widget([
                'name' => $filterModel->formName() . '[date]',
                'presetDropdown' => true,
                'value' => $filterModel->date ?: (new DateTime)->format(DateTimeZoneHelper::DATE_FORMAT) . ' - ' . (new DateTime)->format(DateTimeZoneHelper::DATE_FORMAT),
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'YYYY-MM-DD',
                        'separator' => ' - ',
                    ],
                ],
                'options' => [
                    'class' => 'form-control input-sm',
                    'style' => 'font-size: 12px; height: 30px;',
                ],
            ]),
            'value' => function ($model) {
                return ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('date.formatted');
            },
        ],
        [
            'class' => EventNameColumn::class,
            'width' => '15%',
        ],
        [
            'class' => SourceColumn::class,
            'width' => '10%',
        ],
        [
            'attribute' => 'ip',
            'format' => 'raw',
            'value' => function ($model) {
                return ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('ip');
            },
            'width' => '10%',
        ],
        [
            'class' => TagsColumn::class,
            'filter' => TagsColumn::class,
            'width' => '20%',
        ]
    ],
]);