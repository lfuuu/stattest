<?php

use app\classes\grid\column\billing\TrunkBusinessColumn;
use app\classes\grid\column\billing\TrunkContractColumn;
use app\classes\grid\column\billing\TrunkContractTypeColumn;
use app\classes\grid\column\billing\TrunkContragentColumn;
use app\classes\grid\column\billing\TrunkSuperClientColumn;
use app\classes\grid\column\billing\TrunkTypeColumn;
use app\classes\grid\column\billing\UsageTrunkColumn;
use app\classes\grid\column\universal\ConnectionPointColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var app\models\filter\UsageTrunkFilter $filterModel */

echo Html::formLabel('Транки');

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => 'Транки', 'url' => Url::toRoute(['voip/trunks'])],
    ],
]);

$columns = [
    [
        'attribute' => 'connection_point_id',
        'class' => ConnectionPointColumn::class,
    ],
    [
        'attribute' => 'trunk_ids',
        'class' => TrunkSuperClientColumn::class,
    ],
    [
        'attribute' => 'contragent_id',
        'class' => TrunkContragentColumn::class,
        'trunkId' => $filterModel->trunk_id,
        'connectionPointId' => $filterModel->connection_point_id,
    ],
    [
        'attribute' => 'contract_number',
        'class' => StringColumn::class,
        'format' => 'raw',
        'value' => function ($row) {
            return $row['contract_number'];
        },
    ],
    [
        'attribute' => 'contract_type_id',
        'class' => TrunkContractTypeColumn::class,
        'filterByBusinessProcessId' => $filterModel->business_process_id,
    ],
    [
        'attribute' => 'business_process_id',
        'class' => TrunkBusinessColumn::class,
    ],
    [
        'attribute' => 'trunk_id',
        'class' => UsageTrunkColumn::class,
        'filterByServerIds' => $filterModel->connection_point_id,
    ],
    [
        'attribute' => 'description',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'actual_from',
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'what_is_enabled',
        'label' => 'Ориг / Терм',
        'class' => TrunkTypeColumn::class,
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);