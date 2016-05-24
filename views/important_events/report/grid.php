<?php

use yii\data\ActiveDataProvider;
use app\classes\Html;
use app\widgets\GridViewCustomFilters;
use app\models\important_events\ImportantEvents;
use app\classes\grid\column\important_events\details\DetailColumnFactory;
use app\helpers\DateTimeZoneHelper;
use app\classes\grid\column\important_events\ClientColumn;
use app\classes\grid\column\important_events\EventNameColumn;
use app\classes\grid\column\important_events\SourceColumn;
use app\classes\grid\column\important_events\IpColumn;

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
            'class' => ClientColumn::class,
            'width' => '25%',
        ],
        'date' => [
            'attribute' => 'date',
            'width' => '20%',
            'format' => 'raw',
            'filter' => \kartik\daterange\DateRangePicker::widget([
                'name' => $filterModel->formName() . '[date]',
                'presetDropdown' => true,
                'value' => $filterModel->date ?: (new DateTime)->format('Y-m-d') . ' - ' . (new DateTime)->format('Y-m-d'),
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'YYYY-MM-DD',
                        'separator' => ' - ',
                    ],
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
            'class' => EventNameColumn::class,
            'width' => '25%',
        ],
        [
            'class' => SourceColumn::class,
            'width' => '20%',
        ],
        [
            'class' => IpColumn::class,
            'width' => '10%',
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
