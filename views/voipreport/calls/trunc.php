<?php
/**
 * Звонки в транке. Список звонков
 *
 * @var app\classes\BaseView $this
 * @var CallsRawFilter $filterModel
 */

use app\classes\grid\column\billing\DestinationColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\billing\GeoColumn;
use app\classes\grid\column\billing\MobColumn;
use app\classes\grid\column\billing\OrigColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\billing\TrunkSuperClientColumn;
use app\classes\grid\column\universal\AccountVersionColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\UsageTrunkColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\billing\CallsRaw;
use app\models\filter\CallsRawFilter;
use app\modules\nnp\column\OperatorColumn;
use app\widgets\GridViewExport\GridViewExport;
use yii\db\ActiveQuery;
use yii\widgets\Breadcrumbs;
use app\modules\nnp\column\CityColumn;
use app\modules\nnp\column\RegionColumn;

?>

<?= app\classes\Html::formLabel($this->title = 'Звонки в транке') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Межоператорка (отчеты)'],
        ['label' => $this->title, 'url' => '/voipreport/calls/trunc/'],
    ],
]) ?>

<?php
$columns = [
    [
        'attribute' => 'id',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'server_id',
        'class' => ServerColumn::class,
    ],
    [
        'attribute' => 'trunk_ids', // фейковое поле
        'label' => 'Оператор (суперклиент)',
        'class' => TrunkSuperClientColumn::class,
        'enableSorting' => false,
        'value' => function (CallsRaw $call) {
            return $call->trunk_id;
        },
    ],
    [
        'attribute' => 'trunk_id',
        'class' => TrunkColumn::class,
        'filterByIds' => $filterModel->trunkIdsIndexed,
        'filterByServerIds' => $filterModel->server_id,
        'filterOptions' => [
            'class' => $filterModel->trunk_id ? 'alert-success' : 'alert-danger',
            'title' => 'Фильтр зависит от Региона (точка подключения) и Оператора (суперклиента)',
        ],
    ],
    [
        'attribute' => 'trunk_service_id',
        'class' => UsageTrunkColumn::class,
        'trunkId' => $filterModel->trunk_id,
        'filterOptions' => [
            'title' => 'Фильтр зависит от Транка',
        ],
    ],
    [
        'attribute' => 'connect_time',
        'class' => DateRangeDoubleColumn::class,
        'filterOptions' => [
            'class' => $filterModel->connect_time_from ? 'alert-success' : 'alert-danger',
            'title' => 'У первой даты время считается 00:00, у второй 23:59',
        ],
    ],
    [
        'attribute' => 'src_number',
        'label' => 'Номер А',
        'class' => StringColumn::class,
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
        ],
    ],
    [
        'attribute' => 'dst_number',
        'label' => 'Номер Б',
        'class' => StringColumn::class,
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
        ],
    ],
    [
        'attribute' => 'prefix',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'billed_time',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'rate',
        'class' => FloatRangeColumn::class,
        'format' => ['decimal', 4],
    ],
    [
        'attribute' => 'interconnect_rate',
        'class' => FloatRangeColumn::class,
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Цена минуты с интерконнектом, ¤',
        'format' => ['decimal', 4],
        'value' => function (CallsRaw $calls) {
            return $calls->rate + $calls->interconnect_rate;
        },
    ],
    [
        'attribute' => 'cost',
        'class' => FloatRangeColumn::class,
        'format' => ['decimal', 4],
    ],
    [
        'attribute' => 'interconnect_cost',
        'class' => FloatRangeColumn::class,
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Стоимость с интерконнектом, ¤',
        'format' => ['decimal', 4],
        'value' => function (CallsRaw $calls) {
            return $calls->cost + $calls->interconnect_cost;
        },
    ],
    [
        'attribute' => 'destination_id',
        'class' => DestinationColumn::class,
        'filterByServerId' => $filterModel->server_id,
        'filterOptions' => [
            'title' => 'Фильтр зависит от Региона (точка подключения)',
        ],
    ],
    [
        'attribute' => 'geo_id',
        'class' => GeoColumn::class,
    ],
    [
        'attribute' => 'orig',
        'class' => OrigColumn::class,
    ],
    [
        'attribute' => 'mob',
        'class' => MobColumn::class,
    ],
    [
        'attribute' => 'account_id',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'disconnect_cause',
        'class' => DisconnectCauseColumn::class,
    ],
    [
        'attribute' => 'nnp_country_prefix',
        'class' => CountryColumn::class,
        'indexBy' => 'prefix',
    ],
    [
        'attribute' => 'nnp_ndc',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'account_version',
        'class' => AccountVersionColumn::class,
    ],
    [
        'attribute' => 'nnp_operator_id',
        'class' => OperatorColumn::class,
    ],
    [
        'attribute' => 'nnp_region_id',
        'class' => RegionColumn::class,
    ],
    [
        'attribute' => 'nnp_city_id',
        'class' => CityColumn::class,
    ],
    [
        'attribute' => 'stats_nnp_package_minute_id',
        'format' => 'html',
        'class' => IntegerRangeColumn::class,
        'value' => function (CallsRaw $calls) {
            return $calls->stats_nnp_package_minute_id . '<br/>' .
            ($calls->nnp_package_minute_id ? 'минуты' : '') .
            ($calls->nnp_package_price_id ? 'прайс' : '') .
            ($calls->nnp_package_pricelist_id ? 'прайслист' : '');
        },
    ],
];
?>

<?php

$afterHeader = [];

// при скачивании не считать total
if (!\Yii::$app->request->get('action')) {

// отображаемые колонки Итого в гриде
    $dataProviderSummary = $filterModel->searchCostSummary();
    /** @var ActiveQuery $query */
    $query = $dataProviderSummary->query;
    /** @var CallsRaw $summary */
    $summary = $query->one();
    $summaryColumns = [
        [
            'content' => Yii::t('common', 'Summary'),
            'options' => ['colspan' => 9],
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
        ],
        [
            'content' => sprintf('%.2f', $summary->billed_time_sum),
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->interconnect_cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_with_interconnect_sum),
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
    ];

    $afterHeader = [ // итого
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING], // желтый фон
            'columns' => $summaryColumns,
        ]
    ];
}

$filterColumns = [
    [
        'attribute' => 'is_full_report',
        'class' => YesNoColumn::class,
    ],
];

if (!$filterModel->is_full_report) {
    $columns = array_filter($columns, function($row) {
        return isset($row['attribute'])
            && in_array($row['attribute'],
                ['id', 'trunk_id', 'orig', 'connect_time', 'src_number', 'dst_number', 'billed_time', 'disconnect_cause']
            );
    });

    $summaryColumns = [
        [
            'content' => Yii::t('common', 'Summary'),
        ],
        [
            'content' => sprintf('%.2f', $summary->billed_time_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->interconnect_cost_sum),
        ],
        [
            'content' => sprintf('%.2f', $summary->cost_with_interconnect_sum),
        ],
        [
            'content' => '',
        ],
        [
            'content' => '',
        ],
    ];
    $afterHeader = [ // итого
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING], // желтый фон
            'columns' => $summaryColumns,
        ]
    ];
}



?>

<?php
$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'resizableColumns' => false, // все равно не влезает на экран
    'emptyText' => $filterModel->isFilteringPossible() ? Yii::t('yii', 'No results found.') : 'Выберите транк и время начала разговора',
    'afterHeader' => $afterHeader,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
]);

