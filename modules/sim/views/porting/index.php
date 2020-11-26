<?php
/**
 * Портирование отчёт
 *
 * @var app\classes\BaseView $this
 * @var PhoneHistoryFilter $filterModel
 */

use app\classes\grid\column\DateRangePickerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\sim\columns\PhoneHistory\DateColumn;
use app\modules\sim\columns\PhoneHistory\StateColumn;
use app\modules\sim\filters\PhoneHistoryFilter;
use app\models\danycom\PhoneHistory;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        $this->title = 'Портирование отчёт',
    ],
]) ?>

<?php
$baseView = $this;

$filterColumns = [
    [
        'attribute' => 'date',
        'name' => 'date',
        'label' => 'Дата запроса',
        'class' => DateRangePickerColumn::class,
        'value' => $filterModel->date,
    ],
];

$columns = [
    [
        'attribute' => 'process_id',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'date_request',
    ],
    [
        'attribute' => 'phone_ported',
        'format' => 'html',
        'value' => function (PhoneHistory $phoneHistory) {
            $html = sprintf("%s&nbsp;%s", $phoneHistory->phone_ported ? : '-', $phoneHistory->number);

            return $html;
        },
    ],
    [
        'attribute' => 'process_type',
    ],
    [
        'attribute' => 'from',
    ],
    [
        'attribute' => 'to',
    ],
    [
        'attribute' => 'state',
        'class' => StateColumn::class,
    ],
    [
        'attribute' => 'region',
    ],
    [
        'attribute' => 'date_ported',
    ],
    [
        'attribute' => 'last_message',
    ],
    [
        'attribute' => 'date_sent',
        'class' => DateColumn::class,
    ],
    [
        'attribute' => 'last_sender',
    ],
    [
        'attribute' => 'created_at',
        'class' => DateColumn::class,
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [
        'columns' => $filterColumns,
    ],
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);