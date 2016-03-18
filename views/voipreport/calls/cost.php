<?php
/**
 * Себестоимость. Отчет по направлениям
 *
 * @var \yii\web\View $this
 * @var CallsFilter $filterModel
 */

use app\classes\grid\column\billing\DestinationColumn;
use app\classes\grid\column\billing\MobColumn;
use app\classes\grid\column\billing\OrigColumn;
use app\classes\grid\column\billing\PrefixColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\billing\TrunkSuperСlientColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\UsageTrunkColumn;
use app\classes\grid\GridView;
use app\models\billing\Calls;
use app\models\filter\CallsFilter;
use yii\db\ActiveQuery;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Себестоимость по направлениям') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Межоператорка (отчеты)'],
        ['label' => $this->title, 'url' => '/voipreport/calls/cost/'],
    ],
]) ?>

<?php
// отображаемые колонки в гриде
$columns = [
    [
        'attribute' => 'prefix',
        'class' => IntegerColumn::className(),
        'headerOptions' => ['colspan' => 2],
    ],
    [
        'attribute' => 'prefix_name', // любое имя во избежание дубля с предыдущим
        'class' => PrefixColumn::className(), // используется только для замены
        'headerOptions' => ['class' => 'hidden'], // потому что colspan в первом столбце
        'value' => function (Calls $calls) {
            return $calls->prefix;
        }
    ],
    [
        'attribute' => 'calls_count', // псевдо-поле
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'billed_time_sum', // псевдо-поле
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
    [
        'attribute' => 'acd', // псевдо-поле Средняя длительность
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],

    [
        'attribute' => 'rate',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
//    [
//        'attribute' => 'interconnect_rate',
//        'class' => FloatRangeColumn::className(),
//        'format' => ['decimal', 2],
//    ],
    [
        'attribute' => 'rate_with_interconnect', // псевдо-поле
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],

    [
        'attribute' => 'cost_sum', // псевдо-поле
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
//    [
//        'attribute' => 'interconnect_cost_sum', // псевдо-поле
//        'class' => FloatRangeColumn::className(),
//        'format' => ['decimal', 2],
//    ],
    [
        'attribute' => 'cost_with_interconnect_sum',
        'class' => FloatRangeColumn::className(), // псевдо-поле
        'format' => ['decimal', 2],
    ],

    [
        'attribute' => 'asr', // псевдо-поле Отношение звонков с длительностью ко всем звонкам
        'class' => IntegerRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
];
?>

<?php
// отображаемые колонки Итого в гриде
$dataProviderSummary = $filterModel->searchCostSummary();
/** @var ActiveQuery $query */
$query = $dataProviderSummary->query;
/** @var Calls $summary */
$summary = $query->one();
$summaryColumns = [
    [
        'content' => Yii::t('common', 'Summary'),
        'options' => ['colspan' => 2],
    ],
    [
        'options' => ['class' => 'hidden'], // потому что colspan в первом столбце
    ],
    [
        'content' => $summary->calls_count,
    ],
    [
        'content' => sprintf('%.2f', $summary->billed_time_sum),
    ],
    [
        'content' => sprintf('%.2f', $summary->acd),
    ],
    [
        'content' => '',
    ],
//    [
//        'content' => '',
//    ],
    [
        'content' => '',
    ],
    [
        'content' => sprintf('%.2f', $summary->cost_sum),
    ],
//    [
//        'content' => sprintf('%.2f', $summary->interconnect_cost_sum),
//    ],
    [
        'content' => sprintf('%.2f', $summary->cost_with_interconnect_sum),
    ],
    [
        'content' => sprintf('%.2f', $summary->asr),
    ],
];
?>

<?php
// колонки, от которых выводится только фильтрация
$filterColumns = [
    [
        'attribute' => 'server_id',
        'class' => ServerColumn::className(),
    ],
    [
        'attribute' => 'trunk_ids', // псевдо-поле
        'label' => 'Оператор (суперклиент)',
        'class' => TrunkSuperСlientColumn::className(),
        'enableSorting' => false,
        'value' => function (Calls $call) {
            return $call->trunk_id;
        },
    ],
    [
        'attribute' => 'trunk_id',
        'class' => TrunkColumn::className(),
        'filterByIds' => $filterModel->trunkIdsIndexed,
        'filterByServerId' => $filterModel->server_id,
        'filterOptions' => [
            'class' => $filterModel->trunk_id ? 'alert-success' : 'alert-danger',
            'title' => 'Фильтр зависит от Точки присоединения и Оператора (суперклиента)',
        ],
    ],
    [
        'attribute' => 'trunk_service_id',
        'class' => UsageTrunkColumn::className(),
        'trunkId' => $filterModel->trunk_id,
        'filterOptions' => [
            'title' => 'Фильтр зависит от Транка',
        ],
    ],
    [
        'attribute' => 'destination_id',
        'class' => DestinationColumn::className(),
        'filterByServerId' => $filterModel->server_id,
        'filterOptions' => [
            'title' => 'Фильтр зависит от Точки присоединения',
        ],
    ],
    [
        'attribute' => 'orig',
        'class' => OrigColumn::className(),
    ],
    [
        'attribute' => 'mob',
        'class' => MobColumn::className(),
    ],
    // все закомментированные поля работают, но в целях уменьшения информации не выводятся
//    [
//        'attribute' => 'account_id',
//        'class' => StringColumn::className(),
//    ],
//    [
//        'attribute' => 'cost',
//        'class' => FloatRangeColumn::className(),
//        'format' => ['decimal', 2],
//        'pageSummary' => true,
//    ],
//    [
//        'attribute' => 'interconnect_cost',
//        'class' => FloatRangeColumn::className(),
//        'format' => ['decimal', 2],
//        'pageSummary' => true,
//    ],
    [
        'attribute' => 'connect_time',
        'class' => DateRangeDoubleColumn::className(),
        'filterOptions' => [
            'class' => $filterModel->connect_time_from ? 'alert-success' : 'alert-danger',
            'title' => 'У первой даты время считается 00:00, у второй 23:59',
        ],
    ],
];
?>

<?= GridView::widget([
    'dataProvider' => $filterModel->searchCost(),
    'filterModel' => $filterModel,
    'columns' => $columns,
//    'resizableColumns' => false, // все равно не влезает на экран
    'emptyText' => $filterModel->isFilteringPossible() ? Yii::t('yii', 'No results found.') : 'Выберите транк и время начала разговора',
    'beforeHeader' => [ // фильтры вне грида
        'columns' => $filterColumns,
    ],
    'afterHeader' => [ // итого
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING], // желтый фон
            'columns' => $summaryColumns,
        ]
    ],
]) ?>

