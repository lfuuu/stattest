<?php
/**
 * Звонки в транке. Список звонков
 *
 * @var app\classes\BaseView $this
 * @var CallsFilter $filterModel
 */

use app\classes\grid\column\billing\DestinationColumn;
use app\classes\grid\column\billing\GeoColumn;
use app\classes\grid\column\billing\MobColumn;
use app\classes\grid\column\billing\OrigColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\billing\TrunkSuperСlientColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\UsageTrunkColumn;
use app\classes\grid\GridView;
use app\models\billing\Calls;
use app\models\filter\CallsFilter;
use yii\db\ActiveQuery;
use yii\widgets\Breadcrumbs;

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
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'server_id',
        'class' => ServerColumn::className(),
    ],
    [
        'attribute' => 'trunk_ids', // фейковое поле
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
        'attribute' => 'connect_time',
        'class' => DateRangeDoubleColumn::className(),
        'filterOptions' => [
            'class' => $filterModel->connect_time_from ? 'alert-success' : 'alert-danger',
            'title' => 'У первой даты время считается 00:00, у второй 23:59',
        ],
    ],
    [
        'attribute' => 'src_number',
        'class' => StringColumn::className(),
        'filterOptions' => [
            'title' => 'Можно использовать цифры, _ или . (одна любая цифра), % или * (любая последовательсть цифр, в том числе пустая строка)',
        ],
    ],
    [
        'attribute' => 'dst_number',
        'class' => StringColumn::className(),
        'filterOptions' => [
            'title' => 'Можно использовать цифры, _ или . (одна любая цифра), % или * (любая последовательсть цифр, в том числе пустая строка)',
        ],
    ],
    [
        'attribute' => 'prefix',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'billed_time',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'rate',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
    [
        'attribute' => 'interconnect_rate',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
    [
        'label' => 'Цена минуты с интерконнектом, у.е.',
        'format' => ['decimal', 2],
        'value' => function (Calls $calls) {
            return $calls->rate + $calls->interconnect_rate;
        }
    ],
    [
        'attribute' => 'cost',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
    [
        'attribute' => 'interconnect_cost',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
    ],
    [
        'label' => 'Стоимость с интерконнектом, у.е.',
        'format' => ['decimal', 2],
        'value' => function (Calls $calls) {
            return $calls->cost + $calls->interconnect_cost;
        }
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
        'attribute' => 'geo_id',
        'class' => GeoColumn::className(),
    ],
    [
        'attribute' => 'orig',
        'class' => OrigColumn::className(),
    ],
    [
        'attribute' => 'mob',
        'class' => MobColumn::className(),
    ],
    [
        'attribute' => 'account_id',
        'class' => StringColumn::className(),
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
];
?>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'resizableColumns' => false, // все равно не влезает на экран
    'emptyText' => $filterModel->isFilteringPossible() ? Yii::t('yii', 'No results found.') : 'Выберите транк и время начала разговора',
    'afterHeader' => [ // итого
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING], // желтый фон
            'columns' => $summaryColumns,
        ]
    ],
]) ?>

