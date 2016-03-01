<?php

use yii\data\ActiveDataProvider;
use app\classes\Html;
use app\widgets\GridViewCustomFilters;
use app\models\important_events\ImportantEvents;
use app\classes\grid\column\important_events\details\DetailColumnFactory;
use app\helpers\DateTimeZoneHelper;

/** @var ActiveDataProvider $dataProvider */
/** @var ImportantEvents $filterModel */

echo Html::formLabel('Лог значимых событий');

foreach (\app\models\important_events\ImportantEventsNames::find()->all() as $event) {
    $eventsList[$event->group->title][$event->code] = $event->value;
}

echo GridViewCustomFilters::widget([
    'id' => 'ImportantEvents',
    'formAction' => '/important_events/report',
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function ($model, $key, $index, $column) {
                return GridViewCustomFilters::ROW_COLLAPSED;
            },
            'detail' => function ($model, $key, $index, $column) {
                return implode('<br />', (array) DetailColumnFactory::getColumn($model)) . '<br /><br />';
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
        ],
        [
            'class' => 'app\classes\grid\column\important_events\ClientColumn',
            'width' => '25%',
        ],
        'date' => [
            'attribute' => 'date',
            'width' => '25%',
            'format' => 'raw',
            'filter' => \kartik\daterange\DateRangePicker::widget([
                'name' => $filterModel->formName() . '[date]',
                'presetDropdown' => true,
                'value' => $filterModel->date ?: (new DateTime)->format('Y-m-d') . ' - ' . (new DateTime)->format('Y-m-d'),
                'pluginOptions' => [
                    'format' => 'YYYY-MM-DD',
                ],
                'containerOptions' => [
                    'style' => 'overflow: hidden;',
                    'class' => 'drp-container input-group',
                ],
                'options' => [
                    'style' => 'font-size: 12px; height: 30px;',
                ],
            ]),
            'value' => function ($model, $key, $index, $column) {
                return
                    Yii::$app->formatter->asDateTime(
                        (new DateTime($model->date))
                            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::getUserTimeZone()))
                    );
            },
        ],
        [
            'class' => 'app\classes\grid\column\important_events\EventNameColumn',
            'width' => '25%',
        ],
        [
            'class' => 'app\classes\grid\column\important_events\SourceColumn',
            'width' => '25%',
        ],
    ],
    'pjax' => false,
    'toolbar' => [],
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel' => [
        'type' => GridViewCustomFilters::TYPE_DEFAULT,
    ],
]);
