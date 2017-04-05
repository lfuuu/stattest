<?php
/**
 * @var app\classes\BaseView $this
 * @var \app\models\filter\OperatorPayFilter $filterModel
 */

use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\StringWithLinkColumn;
use app\classes\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

echo app\classes\Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => $this->title, 'url' => '/report/operator-pay/'],
    ],
]);

$baseView = $this;
$columns = [
    [
        'attribute' => 'client_id',
        'label' => $filterModel->getAttributeLabel('client_id'),
        'class' => IntegerColumn::className(),
        'format' => 'raw',
        'value' => function ($bill) {
            return Html::a(
                $bill['name'] . ' (' . $bill['client_id'] . ')',
                ['client/view', 'id' => $bill['client_id']],
                ['target' => '_blank']
            );
        },
        'headerOptions' => ['style' => 'width: 200px']
    ],

    [
        'attribute' => 'bill_no',
        'label' => $filterModel->getAttributeLabel('bill_no'),
        'format' => 'raw',
        'class' => StringColumn::className(),
        'value' => function ($bill) {
            return Html::a(
                $bill['bill_no'],
                ['/', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $bill['bill_no']],
                ['target' => '_blank']
            );
        }
    ],
    [
        'attribute' => 'bill_date',
        'label' => $filterModel->getAttributeLabel('bill_date'),
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'sum',
        'label' => $filterModel->getAttributeLabel('sum'),
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'currency',
        'label' => $filterModel->getAttributeLabel('currency'),
        'class' => CurrencyColumn::className(),
    ],
    [
        'attribute' => 'payment_date',
        'label' => $filterModel->getAttributeLabel('payment_date'),
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'pay_bill_until',
        'label' => $filterModel->getAttributeLabel('pay_bill_until'),
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'comment',
        'label' => $filterModel->getAttributeLabel('comment'),
        'format' => 'raw',
        'class' => StringWithLinkColumn::className(),
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);