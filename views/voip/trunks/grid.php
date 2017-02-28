<?php

use app\classes\grid\column\billing\TrunkContractColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\classes\Html;
use app\classes\grid\GridView;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\ConnectionPointColumn;
use app\classes\grid\column\billing\UsageTrunkColumn;
use app\classes\grid\column\billing\TrunkTypeColumn;
use app\classes\grid\column\billing\TrunkSuperClientColumn;
use app\classes\grid\column\billing\TrunkContragentColumn;
use app\classes\grid\column\billing\TrunkBusinessColumn;
use app\classes\grid\column\billing\TrunkContractTypeColumn;

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
        'label' => 'Точка подключения',
        'class' => ConnectionPointColumn::className(),
        'width' => '10%',
    ],
    [
        'attribute' => 'trunk_ids',
        'label' => 'Супер-клиент',
        'class' => TrunkSuperClientColumn::className(),
        'width' => '20%',
    ],
    [
        'attribute' => 'contragent_id',
        'label' => 'Контрагент',
        'class' => TrunkContragentColumn::className(),
        'trunkId' => $filterModel->trunk_id,
        'connectionPointId' => $filterModel->connection_point_id,
        'width' => '20%',
        'filterOptions' => [
            'class' => $filterModel->trunk_id ? 'alert-success' : 'alert-danger',
            'title' => 'Фильтр зависит от Точки присоединения и Транк',
        ],
    ],
    [
        'attribute' => 'contract_number',
        'label' => '№ договора',
        'class' => StringColumn::className(),
        'format' => 'raw',
        'value' => function ($row) {
            return $row['contract_number'];
        },
        'width' => '10%',
    ],
    [
        'attribute' => 'contract_type_id',
        'label' => 'Тип договора',
        'class' => TrunkContractTypeColumn::className(),
        'filterByBusinessProcessId' => $filterModel->business_process_id,
        'width' => '10%',
        'filterOptions' => [
            'class' => $filterModel->contract_type_id ? 'alert-success' : 'alert-danger',
            'title' => 'Фильтр зависит от Бизнес-процесс',
        ],
    ],
    [
        'attribute' => 'business_process_id',
        'label' => 'Бизнес-процесс',
        'class' => TrunkBusinessColumn::className(),
        'width' => '10%',
    ],
    [
        'attribute' => 'trunk_id',
        'label' => 'Транк',
        'class' => UsageTrunkColumn::className(),
        'filterByServerIds' => $filterModel->connection_point_id,
        'width' => '*',
        'filterOptions' => [
            'class' => $filterModel->trunk_id ? 'alert-success' : 'alert-danger',
            'title' => 'Фильтр зависит от Точки присоединения и Оператора (суперклиента)',
        ],
    ],
    [
        'attribute' => 'what_is_enabled',
        'label' => 'Ориг / Терм',
        'class' => TrunkTypeColumn::className(),
        'width' => '8%',
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'emptyText' => $filterModel->isFilteringPossible() ? Yii::t('yii', 'No results found.') : 'Выберите точку подключения',
]);