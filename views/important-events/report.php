<?php

use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use app\classes\Html;
use app\models\ImportantEvents;

/** @var $dataProvider ActiveDataProvider */
/** @var ImportantEvents $filterModel */

echo Html::formLabel('Логи оповeщений');

echo GridView::widget([
    'id' => 'LkNotifyLog',
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'app\classes\grid\column\important_events\ClientColumn',
            'width' => '5%',
        ],
        'date' => [
            'attribute' => 'date',
            'width' => '30%',
            'format' => 'raw',
            'filter' => \kartik\daterange\DateRangePicker::widget([
                'name' => $filterModel->formName() . '[date]',
                'presetDropdown' => false,
                'hideInput' => true,
                'value' => $filterModel->date ?: (new DateTime('now'))->format('Y-m-d') . ' - ' . (new DateTime('now'))->format('Y-m-d'),
                'pluginOptions' => [
                    'format' => 'YYYY-MM-DD',
                    'ranges' => [
                        'Сегодня' => ['moment().startOf("day")', 'moment()'],
                        'Текущий месяц' => ['moment().startOf("month")', 'moment().endOf("month")'],
                        'Прошлый месяц' => ['moment().subtract(1,"month").startOf("month")', 'moment().subtract(1,"month").endOf("month")'],
                    ],
                ],
                'containerOptions' => [
                    'style' => 'overflow: hidden;',
                    'class' => 'drp-container input-group',
                ]
            ])
        ],
        [
            'class' => 'app\classes\grid\column\important_events\NotificationNameColumn',
            'width' => '30%',
        ],
        [
            'attribute' => 'balance',
            'width' => '10%',
        ],
        [
            'attribute' => 'limit',
            'width' => '10%',
        ],
        [
            'attribute' => 'value',
            'width' => '10%',
        ],
    ],
    'pjax' => false,
    'toolbar'=> [],
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel'=>[
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
?>