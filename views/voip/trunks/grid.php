<?php

use app\classes\grid\column\billing\TrunkBusinessColumn;
use app\classes\grid\column\billing\TrunkContractTypeColumn;
use app\classes\grid\column\billing\TrunkContragentColumn;
use app\classes\grid\column\billing\TrunkSuperClientColumn;
use app\classes\grid\column\billing\TrunkTypeColumn;
use app\classes\grid\column\billing\UsageTrunkColumn;
use app\classes\grid\column\FederalDistrictColumn;
use app\classes\grid\column\universal\ConnectionPointColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\billing\Trunk;
use app\models\billing\TrunkGroup;
use yii\db\Query;
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

$db = TrunkGroup::getDb();
$dataProvider = $filterModel->search();

// Клонирование SqlQuery, получаем id's транков, по которым необходимо получить правила и префикс-листы
$query = clone $dataProvider->query;
$trunkIds = $query->select('trunk.trunk_id')->column();
// Получение и перестройка необходимых данных с учетом связей транков, правил транков и префикс-листов
$relations = Trunk::getRulesAndPrefixlistRelations($trunkIds);
$cache = Trunk::restructRulesAndPrefixlistRelations($relations);
unset($query, $relations, $trunkIds);

$columns = [
    [
        'attribute' => 'number_a_orig',
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($data) use ($cache) {
            $trunkId = $data['trunk_id'];
            return isset($cache[$trunkId]) ?
                Trunk::graphicDistributionOfRules($cache[$trunkId], true, false) : '';
        },
    ],
    [
        'attribute' => 'number_b_orig',
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($data) use ($cache) {
            $trunkId = $data['trunk_id'];
            return isset($cache[$trunkId]) ?
                Trunk::graphicDistributionOfRules($cache[$trunkId], true, true) : '';
        },
    ],
    [
        'attribute' => 'number_a_term',
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($data) use ($cache) {
            $trunkId = $data['trunk_id'];
            return isset($cache[$trunkId]) ?
                Trunk::graphicDistributionOfRules($cache[$trunkId], false, false) : '';
        },
    ],
    [
        'attribute' => 'number_b_term',
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($data) use ($cache) {
            $trunkId = $data['trunk_id'];
            return isset($cache[$trunkId]) ?
                Trunk::graphicDistributionOfRules($cache[$trunkId], false, true) : '';
        },
    ],
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
        'attribute' => 'group_orig_trunk',
        'enableSorting' => false,
        'value' => function ($data) use ($db) {
            $groups = (new Query())
                ->select(['name' => "string_agg(tg.name, ', ')"])
                ->from([
                    'tti' => 'auth.trunk_group_item',
                    'tg' => 'auth.trunk_group',
                ])
                ->where([
                    'tti.trunk_id' => $data['trunk_id'],
                ])
                ->andWhere('tg.id = tti.trunk_group_id')
                ->scalar($db);
            return $groups ?: '';
        },
    ],
    [
        'attribute' => 'group_term_trunk',
        'enableSorting' => false,
        'value' => function ($data) use ($db) {
            $groups = (new Query())
                ->select(['name' => "string_agg(tg.name, ', ')"])
                ->from([
                    'ttr' => 'auth.trunk_trunk_rule',
                    'tg' => 'auth.trunk_group',
                ])
                ->where([
                    'ttr.trunk_id' => $data['trunk_id'],
                ])
                ->andWhere('tg.id = ttr.trunk_group_id')
                ->scalar($db);
            return $groups ?: '';
        },
    ],
    [
        'attribute' => 'federal_district',
        'class' => FederalDistrictColumn::class,
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
    [
        'attribute' => 'comment',
        'value' => function ($row) {
            return $row['comment'] ?: '';
        },
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
]);