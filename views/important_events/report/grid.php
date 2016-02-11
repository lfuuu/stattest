<?php

use yii\data\ActiveDataProvider;
use app\classes\Html;
use app\widgets\GridViewCustomFilters;
use app\models\important_events\ImportantEvents;
use app\classes\grid\column\important_events\details\DetailColumnFactory;

/** @var ActiveDataProvider $dataProvider */
/** @var ImportantEvents $filterModel */

echo Html::formLabel('Логи оповeщений');

foreach (\app\models\important_events\ImportantEventsNames::find()->all() as $event) {
    $eventsList[$event->group->title][$event->code] = $event->value;
}

echo GridViewCustomFilters::widget([
    'id' => 'ImportantEvents',
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
                'value' => $filterModel->date ?: (new DateTime('first day of this month'))->format('Y-m-d') . ' - ' . (new DateTime('last day of this month'))->format('Y-m-d'),
                'pluginOptions' => [
                    'format' => 'YYYY-MM-DD',
                    'ranges' => [
                        'Текущий месяц' => ['moment().startOf("month")', 'moment().endOf("month")'],
                        'Прошлый месяц' => ['moment().subtract(1,"month").startOf("month")', 'moment().subtract(1,"month").endOf("month")'],
                        'Сегодня' => ['moment().startOf("day")', 'moment()'],
                    ],
                ],
                'containerOptions' => [
                    'style' => 'overflow: hidden;',
                    'class' => 'drp-container input-group',
                ],
                'options' => [
                    'style' => 'font-size: 12px; height: 30px;',
                ],
            ])
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

?>