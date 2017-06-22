<?php
/**
 * @var app\classes\BaseView $this
 * @var \app\models\filter\PayReportFilter $filterModel
 */

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\DropdownColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\OrganizationColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\UserColumn;
use app\classes\grid\GridView;
use app\models\Payment;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

echo app\classes\Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => $this->title, 'url' => '/report/accounting/pay-report/'],
    ],
]);

$baseView = $this;

$columns = [
    [
        'attribute' => 'client_id',
        'label' => $filterModel->getAttributeLabel('client_id'),
        'class' => IntegerColumn::className(),
        'format' => 'raw',
        'value' => function (Payment $payment) {
            return Html::a(
                $payment->client_id,
                ['client/view', 'id' => $payment->client_id],
                ['target' => '_blank']
            );
        },
        'headerOptions' => ['style' => 'width: 80px']
    ],
    [
        'attribute' => 'client_name',
        'label' => $filterModel->getAttributeLabel('client_name'),
        'format' => 'raw',
        'value' => function (Payment $payment) {
            return Html::tag('small',
                Html::a(
                    $payment->client->contragent->name,
                    ['client/view', 'id' => $payment->client_id],
                    ['target' => '_blank', 'title' => $payment->client->contragent->name_full]
                )
            );
        },
        'headerOptions' => ['style' => 'width: 140px']
    ],
    [
        'attribute' => 'organization_id',
        'label' => $filterModel->getAttributeLabel('organization_id'),
        'class' => OrganizationColumn::className(),
        'format' => 'raw',
        'value' => function (Payment $payment) {
            return $payment->client->contract->organization_id;
        },
        'headerOptions' => ['style' => 'width: 180px']
    ],
    [
        'attribute' => 'bill_no',
        'format' => 'raw',
        'class' => StringColumn::className(),
        'value' => function (Payment $payment) {
            return $payment->bill_no ? Html::a(
                $payment->bill_no,
                ['/', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $payment->bill_no],
                ['target' => '_blank']
            ) : '';
        },
        'headerOptions' => ['style' => 'width: 120px']
    ],
    [
        'attribute' => 'sum',
        'class' => IntegerRangeColumn::className(),
        'headerOptions' => ['style' => 'width: 100px']
    ],
    [
        'attribute' => 'currency',
        'class' => CurrencyColumn::className(),
    ],

    [
        'attribute' => 'payment_date',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'oper_date',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'add_date',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'type',
        'class' => DropdownColumn::className(),
        'filter' => $filterModel->getTypeList(),
        'value' => function (Payment $payment) {
            return $payment->type == Payment::TYPE_ECASH ? Payment::TYPE_ECASH . '_' . $payment->ecash_operator : $payment->type;
        }
    ],
    [
        'attribute' => 'payment_no',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'comment',
        'format' => 'raw',
        'class' => StringColumn::className(),
        'value' => function (Payment $payment) {
            return Html::tag('small', $payment->comment);
        }
    ],
    [
        'attribute' => 'add_user',
        'format' => 'raw',
        'class' => UserColumn::className(),
        'indexBy' => 'id',
        'value' => function (Payment $payment) {
            return $payment->add_user && $payment->addUser ?
                Html::tag('div', $payment->addUser->user, ['title' => $payment->addUser->name]) :
                '';
        }
    ],
];

$filterColumns = [
    [
        'attribute' => 'sort_field',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => $filterModel->getSortDateList(),
        'class' => DataColumn::className()
    ],
    [
        'attribute' => 'sort_direction',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => $filterModel->getSortDirection(),
        'class' => DataColumn::className()
    ],
];

$dataProvider = $filterModel->search();
?>

<div class="well">
    <div class="span12"><b>Итого: <?=$filterModel->total?></b></div>
</div>

<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'beforeHeader' => [
        'columns' => $filterColumns,
    ],
]);