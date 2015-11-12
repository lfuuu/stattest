<?php

use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use app\classes\Html;
use app\models\ClientAccount;
use app\models\notifications\NotificationLog;

/** @var $dataProvider ActiveDataProvider */
/** @var NotificationLog $filterModel */

echo Html::formLabel('Логи оповeщений');

echo GridView::widget([
    'id' => 'LkNotifyLog',
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function ($model, $key, $index, $column) {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model, $key, $index, $column) {
                $clientAccount = ClientAccount::findOne($model['client_id']);
                $notification = NotificationLog::findOne($model['id']);

                return Yii::$app->controller->renderPartial('expand-details', [
                    'model' => $model,
                    'client' => $clientAccount,
                    'notification' => $notification,
                ]);
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
            'expandOneOnly' => true,
        ],
        [
            'class' => 'app\classes\grid\column\notifications\ClientColumn',
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
            'class' => 'app\classes\grid\column\notifications\NotificationNameColumn',
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