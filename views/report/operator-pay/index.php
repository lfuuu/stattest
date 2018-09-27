<?php
/**
 * @var app\classes\BaseView $this
 * @var \app\models\filter\OperatorPayFilter $filterModel
 */

use app\classes\grid\column\universal\BusinessColumn;
use app\classes\grid\column\universal\BusinessProcessColumn;
use app\classes\grid\column\universal\BusinessProcessStatusColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\StringWithLinkColumn;
use app\classes\grid\column\universal\UserColumn;
use app\classes\grid\GridView;
use app\models\filter\OperatorPayFilter;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

echo app\classes\Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => $this->title, 'url' => '/report/operator-pay/'],
    ],
]);

$baseView = $this;

$filterColumns = [
    [
        'attribute' => 'organization_id',
        'class' => \app\classes\grid\column\universal\OrganizationColumn::class,
    ],
    [
        'attribute' => 'bill_type',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => $filterModel->getBillTypeList(),
        'class' => \app\classes\grid\column\DataColumn::class
    ],
    [
        'attribute' => 'checking_bill_state',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => [
            OperatorPayFilter::STATE_BILL_ALL => 'Все',
            OperatorPayFilter::STATE_BILL_PAID => 'Оплачен',
            OperatorPayFilter::STATE_BILL_UNPAID => 'Не оплачен',
        ],
        'class' => \app\classes\grid\column\DataColumn::class
    ],
    [
        'attribute' => 'payment_verified',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => [
            OperatorPayFilter::STATE_PAYMENT_ALL => 'Все',
            OperatorPayFilter::STATE_PAYMENT_VERIFIED => 'Проверена',
            OperatorPayFilter::STATE_PAYMENT_UNVERIFIED => 'Не проверена',
        ],
        'class' => \app\classes\grid\column\DataColumn::class
    ],
];

$columns = [
    [
        'attribute' => 'client_id',
        'label' => $filterModel->getAttributeLabel('client_id'),
        'class' => IntegerColumn::class,
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
        'class' => StringColumn::class,
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
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'sum',
        'label' => $filterModel->getAttributeLabel('sum'),
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'currency',
        'label' => $filterModel->getAttributeLabel('currency'),
        'class' => CurrencyColumn::class,
    ],
    [
        'attribute' => 'payment_date',
        'label' => $filterModel->getAttributeLabel('payment_date'),
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'pay_bill_until',
        'label' => $filterModel->getAttributeLabel('pay_bill_until'),
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'label' => 'Подразделение',
        'attribute' => 'business_id',
        'class' => BusinessColumn::class,
    ],
    [
        'label' => 'Бизнес-процесс',
        'attribute' => 'business_process_id',
        'class' => BusinessProcessColumn::class,
    ],
    [
        'label' => 'Статус бизнес-процесса',
        'attribute' => 'business_process_status_id',
        'class' => BusinessProcessStatusColumn::class,
    ],
    [
        'label' => 'Менеджер',
        'attribute' => 'manager',
        'class' => UserColumn::class,
    ],
    [
        'label' => 'Ак. менеджер',
        'attribute' => 'account_manager',
        'class' => UserColumn::class,
    ],
    [
        'attribute' => 'comment',
        'label' => $filterModel->getAttributeLabel('comment'),
        'format' => 'raw',
        'class' => StringWithLinkColumn::class,
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'afterHeader' => [
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING],
            'columns' => [
                [
                    'content' => Yii::t('common', 'Summary'),
                    'options' => ['colspan' => 3, 'class' => 'text-left'],
                ],
                [
                    'content' => $dataProvider->query->sum('b.sum'),
                ],
                [
                    'content' => '',
                    'options' => ['colspan' => 9, 'class' => 'text-left'],
                ],
            ],
        ]
    ],
    'beforeHeader' => [
        'columns' => $filterColumns,
    ],
]);