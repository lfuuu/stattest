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
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'account_tariff_id',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function(Log $log) {
            return Html::a($log->account_tariff_id, '/uu/account-tariff/edit?id=' . $log->account_tariff_id);
        },
    ],
    [
        'attribute' => 'voip_number',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'start_dt',
        'class' => DateTimeRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'disconnect_dt',
        'class' => DateTimeRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'stop_dt',
        'class' => DateTimeRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'user_agent',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'ip',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'url',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'referrer',
        'class' => StringColumn::class,
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