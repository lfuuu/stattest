<?php
/**
 * Статистика логов CallTracking
 *
 * @var \app\classes\BaseView $this
 * @var LogFilter $filterModel
 */

use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\callTracking\filter\LogFilter;
use app\modules\callTracking\models\Log;
use app\widgets\GridViewExport\GridViewExport;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$this->title = 'CallTracking. Статистика';
?>

<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => 'CallTracking',
        ],
        [
            'label' => 'Статистика',
            'url' => Url::to(['/callTracking/log/'])
        ],
    ],
]) ?>

<?php

$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'account_tariff_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function(Log $log) {
            return Html::a($log->account_tariff_id, '/uu/account-tariff/edit?id=' . $log->account_tariff_id);
        },
    ],
    [
        'attribute' => 'voip_number',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'start_dt',
        'class' => DateTimeRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'disconnect_dt',
        'class' => DateTimeRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'stop_dt',
        'class' => DateTimeRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'user_agent',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'ip',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'url',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'referrer',
        'class' => StringColumn::className(),
    ],
];

/** @var LogFilter $dataProvider */
$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);