<?php

/**
 * @var app\classes\BaseView $this
 * @var ArrayDataProvider $dataProvider
 * @var BalanceReport $filterModel
 */

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\Html;
use app\classes\grid\GridView;
use app\forms\voipreport\BalanceReport;
use app\models\Business;
use app\models\ContractType;
use kartik\select2\Select2;
use yii\data\ArrayDataProvider;
use yii\widgets\Breadcrumbs;
use \app\models\Currency;


echo Html::formLabel($title = 'Баланс');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Межоператорка (отчеты)'],
        ['label' => $title, 'url' => '/voipreport/balance-report'],
    ],
]);

$columns = [
    [
        'attribute' => 'id',
        'value' => function ($model) {
            return Html::a($model['id'], ['client/view', 'id' => $model['id']]);
        },
        'class' => DataColumn::class,
        'format' => 'raw'
    ],
    [
        'attribute' => 'account_manager',
        'class' => DataColumn::class,
    ],
    [
        'attribute' => 'contract_type_name',
        'filter' => Select2::widget([
            'data' => ContractType::getList($filterModel->bp_id, true),
            'model' => $filterModel,
            'attribute' => 'contract_type_id'
        ])
    ],
    [
        'attribute' => 'balance',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'realtime_balance',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'credit',
        'value' => function ($model) {
            return Html::tag('span', $model['credit'], ($model['credit'] >= 0 && $model['realtime_balance'] + $model['credit'] < 0) ? ['class' => 'text-danger'] : []);
        },
        'class' => IntegerRangeColumn::class,
        'format' => 'raw'
    ],
    [
        'attribute' => 'currency',
        'filter' => Html::activeDropDownList(
            $filterModel,
            'currency',
            Currency::getList(true),
            ['class' => 'form-control']
        ),
        'width' => '5%'
    ],
    [
        'attribute' => 'name_full',
        'format' => 'raw',
    ],
    [
        'attribute' => 'b_id',
        'value' => 'b_name',
        'filter' => Html::activeDropDownList(
            $filterModel,
            'b_id',
            Business::getList(true),
            ['id' => 'business_id', 'class' => 'select2']
        ),
        'format' => 'raw',
        'width' => '10%'
    ],
    [
        'attribute' => 'bp_id',
        'value' => 'bp_name',
        'filter' => Html::activeDropDownList(
            $filterModel,
            'bp_id',
            ['' => '----'],
            ['id' => 'business_process_id', 'class' => 'select2']
        ),
        'format' => 'raw',
        'width' => '10%'
    ],
    [
        'attribute' => 'bps_id',
        'value' => 'bps_name',
        'filter' => Html::activeDropDownList(
            $filterModel,
            'bps_id',
            ['' => '----'],
            ['id' => 'business_process_status_id', 'class' => 'select2']
        ),
        'format' => 'raw',
        'width' => '10%'
    ],
    [
        'attribute' => 'income_sum',
        'class' => IntegerRangeColumn::class,
        'format' => 'raw',
    ],
    [
        'attribute' => 'outcome_sum',
        'class' => IntegerRangeColumn::class,
        'format' => 'raw',
    ],
    [
        'attribute' => 'inc_out_sum',
        'class' => IntegerRangeColumn::class,
        'format' => 'raw',
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
]);

