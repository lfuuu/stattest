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
        'class' => DataColumn::class,
    ],
    [
        'attribute' => 'account_manager',
        'class' => DataColumn::class,
    ],
    [
        'attribute' => 'balance',
        'class' => IntegerRangeColumn::class,
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

