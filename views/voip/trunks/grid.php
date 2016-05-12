<?php

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
        'width' => '20%',
    ],
    [
        'attribute' => 'contract_id',
        'label' => '№ договора',
        'class' => StringColumn::className(),
        'format' => 'raw',
        'value' => function ($row) {
            return Html::a($row['contract_id'], Url::toRoute(['contract/edit', 'id' => $row['contract_id']]), ['target' => '_blank']);
        },
        'width' => '5%',
    ],
    [
        'attribute' => 'contract_type_id',
        'label' => 'Тип договора',
        'class' => TrunkContractTypeColumn::className(),
        'filterByBusinessProcessId' => $filterModel->business_process_id,
        'width' => '10%',
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
        'width' => '*',
    ],
    [
        'attribute' => 'what_is_enabled',
        'label' => 'Ориг / Терм',
        'class' => TrunkTypeColumn::className(),
        'width' => '8%',
        'options' => ['class' => 'text-center',],
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);