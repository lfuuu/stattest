<?php
/**
 * Отчет по звонкам в транке. Список звонков
 *
 * @var \yii\web\View $this
 * @var CallsFilter $filterModel
 */

use app\classes\grid\column\billing\DestinationColumn;
use app\classes\grid\column\billing\GeoColumn;
use app\classes\grid\column\billing\MobColumn;
use app\classes\grid\column\billing\OrigColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\billing\TrunkSuperСlientColumn;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\UsageTrunkColumn;
use app\classes\grid\GridView;
use app\models\billing\Calls;
use app\models\filter\CallsFilter;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Отчет по звонкам в транке') ?>
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
        'pageSummary' => Yii::t('common', 'Page Summary'),
        'pageSummaryOptions' => ['colspan' => 7],
    ],
    [
        'attribute' => 'server_id',
        'class' => ServerColumn::className(),
        'pageSummaryOptions' => ['class' => 'hidden'], // потому что colspan в первом столбце
    ],
    [
        'attribute' => 'trunk_ids', // фейковое поле
        'label' => 'Оператор (суперклиент)',
        'class' => TrunkSuperСlientColumn::className(),
        'enableSorting' => false,
        'value' => function (Calls $call) {
            return $call->trunk_id;
        },
        'pageSummaryOptions' => ['class' => 'hidden'], // потому что colspan в первом столбце
    ],
    [
        'attribute' => 'trunk_id',
        'class' => TrunkColumn::className(),
        'filterByIds' => $filterModel->trunkIdsIndexed,
        'filterByServerId' => $filterModel->server_id,
        'pageSummaryOptions' => ['class' => 'hidden'], // потому что colspan в первом столбце
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
        'class' => DateTimeRangeDoubleColumn::className(),
        'pageSummaryOptions' => ['class' => 'hidden'], // потому что colspan в первом столбце
        'filterOptions' => ['class' => $filterModel->connect_time_from ? 'alert-success' : 'alert-danger'],
    ],
    [
        'attribute' => 'src_number',
        'class' => StringColumn::className(),
        'pageSummaryOptions' => ['class' => 'hidden'], // потому что colspan в первом столбце
    ],
    [
        'attribute' => 'dst_number',
        'class' => StringColumn::className(),
        'pageSummaryOptions' => ['class' => 'hidden'], // потому что colspan в первом столбце
    ],
    [
        'attribute' => 'billed_time',
        'class' => IntegerRangeColumn::className(),
        'pageSummary' => true,
    ],
    [
        'attribute' => 'rate',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_AVG,
    ],
    [
        'attribute' => 'interconnect_rate',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_AVG,
    ],
    [
        'label' => 'Цена минуты с интерконнектом, у.е.',
        'format' => ['decimal', 2],
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_AVG,
        'value' => function (Calls $calls) {
            return $calls->rate + $calls->interconnect_rate;
        }
    ],
    [
        'attribute' => 'cost',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
        'pageSummary' => true,
    ],
    [
        'attribute' => 'interconnect_cost',
        'class' => FloatRangeColumn::className(),
        'format' => ['decimal', 2],
        'pageSummary' => true,
    ],
    [
        'label' => 'Стоимость с интерконнектом, у.е.',
        'format' => ['decimal', 2],
        'pageSummary' => true,
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
<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'showPageSummary' => true,
    'resizableColumns' => false, // все равно не влезает на экран
    'emptyText' => $filterModel->isFilteringPossible() ? Yii::t('yii', 'No results found.') : 'Выберите транк и время начала разговора',
]) ?>

