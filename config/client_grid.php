<?php

$defaultQueryParams = [
    'from' => [
        'clients c',
    ],
    'select' => [
        'c.status',
        'c.id',
        'c.contract_id',
        'cg.name AS company',
        'cr.manager',
        'cr.account_manager',
        'c.support',
        'c.telemarketing',
        'c.sale_channel',
        'DATE(c.created) AS created',
        'c.currency',
        'c.region',
    ],
    'join' => [
        ['INNER JOIN', 'client_contract cr', 'c.contract_id = cr.id'],
        ['INNER JOIN', 'client_contragent cg', 'cr.contragent_id = cg.id'],
    ],
    'where' => [
        'and',
    ],
    'orderBy' => [
        'c.created' => SORT_DESC
    ]
];

$defaultColumnsParams = [
    'status' => [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => function ($data) {
            return '<span class="btn btn-grid" style="background:' . $data->statusColor . '" title="' . $data->statusName . '">&nbsp;</span>';
        },
        'filterType' => \kartik\grid\GridView::FILTER_COLOR
    ],
    'id' => [
        'attribute' => 'id',
        'filter' => function(){
            return '<input name="id" class="form-control" value="'.\Yii::$app->request->get('companyName').'" />';
        },
        'format' => 'raw',
        'value' => function ($data) {
            return '<a href="/client/view?id=' . $data->id . '">' . $data->id . '</a>';
        }
    ],
    'company' => [
        'attribute' => 'company',
        'format' => 'raw',
        'value' => function ($data) {
            return '<a href="/client/view?id=' . $data->id . '">' . $data->contract->contragent->name . '</a>';
        },
        'filter' => function() {
            return '<input name="companyName" id="searchByCompany" value="' . \Yii::$app->request->get('companyName') . '" class="form-control" />';
        },
    ],
    'created' => [
        'attribute' => 'created',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->created;
        },
        'filter' => function () {
            return \kartik\daterange\DateRangePicker::widget([
                'name' => 'createdDate',
                'presetDropdown' => true,
                'hideInput' => true,
                'value' => \Yii::$app->request->get('created'),
                'containerOptions' => [
                    'style' => 'width:300px;',
                    'class' => 'drp-container input-group',
                ]
            ]);
        }
    ],/*
    'block_date' => [
        'attribute' => 'block_date',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->block_date;
        }
    ],*/
    'service' => [
        'attribute' => 'service',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->service;
        },
        'filter' => function () {
            return \yii\helpers\Html::dropDownList(
                'service',
                \Yii::$app->request->get('service'),
                [
                    'emails' => 'emails',
                    'tech_cpe' => 'tech_cpe',
                    'usage_extra' => 'usage_extra',
                    'usage_ip_ports' => 'usage_ip_ports',
                    'usage_sms' => 'usage_sms',
                    'usage_virtpbx' => 'usage_virtpbx',
                    'usage_voip' => 'usage_voip',
                    'usage_welltime' => 'usage_welltime',
                ],
                ['class' => 'form-control', 'prompt' => '-Не выбрано-']
            );
        },
    ],
    'abon' => [
        'attribute' => 'abon',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->abon;
        }
    ],
    'over' => [
        'attribute' => 'over',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->over;
        }
    ],
    'total' => [
        'attribute' => 'total',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->total;
        }
    ],
    'abon1' => [
        'attribute' => 'abon1',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->abon1;
        }
    ],
    'over1' => [
        'attribute' => 'over1',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->over1;
        }
    ],
    'abondiff' => [
        'attribute' => 'abondiff',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->abondiff;
        }
    ],
    'overdiff' => [
        'attribute' => 'overdiff',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->overdiff;
        }
    ],
    'bill_date' => [
        'attribute' => 'bill_date',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->bill_date;
        },
        'filter' => function () {
            return \kartik\daterange\DateRangePicker::widget([
                'name' => 'bill_date',
                'presetDropdown' => true,
                'hideInput' => true,
                'value' => \Yii::$app->request->get('bill_date'),
                'containerOptions' => [
                    'style' => 'width:300px;',
                    'class' => 'drp-container input-group',
                ]
            ]);
        }
    ],
    'manager' => [
        'attribute' => 'manager',
        'format' => 'raw',
        'value' => function ($data) {
            return '<a href="index.php?module=users&m=user&id=' . $data->userManager->user . '">' . $data->userManager->name . '</a>';
        },
        'filter' => function () {
            return \kartik\widgets\Select2::widget([
                'name' => 'manager',
                'data' => \app\models\User::getManagerList(),
                'value' => \Yii::$app->request->get('manager'),
                'options' => ['placeholder' => 'Начните вводить фамилию'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
        },
    ],
    'account_manager' => [
        'attribute' => 'account_manager',
        'format' => 'raw',
        'value' => function ($data) {
            return '<a href="index.php?module=users&m=user&id=' . $data->userAccountManager->user . '">' . $data->userAccountManager->name . '</a>';
        },
        'filter' => function () {
            return \kartik\widgets\Select2::widget([
                'name' => 'account_manager',
                'value' => \Yii::$app->request->get('account_manager'),
                'data' => \app\models\User::getAccountManagerList(),
                'options' => ['placeholder' => 'Начните вводить фамилию'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
        },
    ],
    'currency' => [
        'attribute' => 'currency',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->currency;
        },
        'filter' => function () {
            return \yii\helpers\Html::dropDownList(
                'currency',
                \Yii::$app->request->get('currency'),
                \app\models\Currency::map(),
                ['class' => 'form-control', 'prompt' => '-Не выбрано-']
            );
        },
    ],
    'sale_channel' => [
        'attribute' => 'sale_channel',
        'format' => 'raw',
        'value' => function ($data) {
            return '<a href="/sale-channel/edit?id=' . $data->sale_channel . '">' . $data->channelName . '</a>';
        },
        'filter' => function () {
            return \kartik\widgets\Select2::widget([
                'name' => 'account_manager',
                'data' => \app\models\SaleChannel::getList(),
                'value' => \Yii::$app->request->get('sale_channel'),
                'options' => ['placeholder' => 'Начните вводить название'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
        },
    ],
    'region' => [
        'attribute' => 'region',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->accountRegion->name;
        },
        'filter' => function () {
            return \yii\helpers\Html::dropDownList(
                'regionId',
                \Yii::$app->request->get('regionId'),
                \app\models\Region::getList(),
                ['class' => 'form-control', 'prompt' => '-Не выбрано-']
            );
        },
    ],
];

$labels = array(
    'status' => '#',
    'id' => 'ИД',
    'company' => 'Компания',
    'created' => 'Заведен',
    'block_date' => 'Дата блокировки',
    'service' => 'Услуга',
    'abon' => 'Абон.(пред.)',
    'over' => 'Прев.(пред.)',
    'total' => 'Всего',
    'abon1' => 'Абон.(тек.)',
    'over1' => 'Прев.(тек.)',
    'abondiff' => 'Абон.(diff)',
    'overdiff' => 'Прев.(diff)',
    'bill_date' => 'Дата платежа',
);

$grid_settings = [];

$current_dir = dirname(__FILE__);

$grid_settings += (array) require_once $current_dir . '/client_grids/telecom.php';
$grid_settings += (array) require_once $current_dir . '/client_grids/internet_shop.php';
$grid_settings += (array) require_once $current_dir . '/client_grids/provider.php';
$grid_settings += (array) require_once $current_dir . '/client_grids/partner.php';
$grid_settings += (array) require_once $current_dir . '/client_grids/internal_office.php';
$grid_settings += (array) require_once $current_dir . '/client_grids/operator.php';
$grid_settings += (array) require_once $current_dir . '/client_grids/welltime.php';
$grid_settings += (array) require_once $current_dir . '/client_grids/custom.php';

return [
    'labels' => $labels,
    'defaultQueryParams' => $defaultQueryParams,
    'defaultColumnsParams' => $defaultColumnsParams,
    'data' => $grid_settings,
];