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
    ],
    'block_date' => [
        'attribute' => 'block_date',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->block_date;
        }
    ],
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

return
    [
        'labels' => $labels,
        'defaultQueryParams' => $defaultQueryParams,
        'defaultColumnsParams' => $defaultColumnsParams,
        'data' => [
            19 =>
                array(
                    'id' => 19,
                    'name' => \Yii::t('app', 'Заказ услуг'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 19',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'negotiations',
                    'color' => '#C4DF9B',
                ),
            8 =>
                array(
                    'id' => 8,
                    'name' => \Yii::t('app', 'Подключаемые'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 8',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'connecting',
                    'color' => '#F49AC1',
                ),
            9 =>
                array(
                    'id' => 9,
                    'name' => \Yii::t('app', 'Включенные'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 9',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'account_manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'work',
                    'color' => '',
                ),
            10 =>
                array(
                    'id' => 10,
                    'name' => \Yii::t('app', 'Отключенные'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 10',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'closed',
                    'color' => '#FFFFCC',
                ),
            11 =>
                array(
                    'id' => 11,
                    'name' => \Yii::t('app', 'Отключенные за долги'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.is_blocked' => 1,
                                'cr.business_process_status_id = 9',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'block_date',
                        'currency',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => 'debt',
                    'color' => '#C00000',
                ),
            21 =>
                array(
                    'id' => 21,
                    'name' => \Yii::t('app', 'Автоблокировка'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.is_blocked' => 1,
                                0 =>
                                    array(
                                        'not in',
                                        'cr.business_process_status_id',
                                        2 =>
                                            array(
                                                0 => 8,
                                                1 => 11,
                                            ),
                                    ),
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'currency',
                        'block_date',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            22 =>
                array(
                    'id' => 22,
                    'name' => \Yii::t('app', 'Мусор'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 22',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'trash',
                    'color' => '#a5e934',
                ),
            23 =>
                array(
                    'id' => 23,
                    'name' => \Yii::t('app', 'Не привязанные'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 0',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            27 =>
                array(
                    'id' => 27,
                    'name' => \Yii::t('app', 'Техотказ'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 27',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'tech_deny',
                    'color' => '#996666',
                ),
            28 =>
                array(
                    'id' => 28,
                    'name' => \Yii::t('app', 'Отказ'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 28',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'deny',
                    'color' => '#A0A0A0',
                ),
            29 =>
                array(
                    'id' => 29,
                    'name' => \Yii::t('app', 'Дубликат'),
                    'grid_business_process_id' => 1,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 2',
                                'cr.business_process_status_id = 29',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'double',
                    'color' => '#60a0e0',
                ),
            1 =>
                array(
                    'id' => 1,
                    'name' => \Yii::t('app', 'Входящие'),
                    'grid_business_process_id' => 2,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' => 'income',
                                'cr.contract_type_id = 2',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => true,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => 'income',
                    'color' => '#CCFFFF',
                ),
            31 =>
                array(
                    'id' => 31,
                    'name' => \Yii::t('app', 'Входящие'),
                    'grid_business_process_id' => 2,
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'income',
                    'color' => '#CCFFFF',
                ),
            2 =>
                array(
                    'id' => 2,
                    'name' => \Yii::t('app', 'В стадии переговоров'),
                    'grid_business_process_id' => 2,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' => 'negotiations',
                                'cr.contract_type_id = 2',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => 'negotiations',
                    'color' => '#C4DF9B',
                ),
            3 =>
                array(
                    'id' => 3,
                    'name' => \Yii::t('app', 'Тестируемые'),
                    'grid_business_process_id' => 2,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' => 'testing',
                                'cr.contract_type_id = 2',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => 'testing',
                    'color' => '#6DCFF6',
                ),
            4 =>
                array(
                    'id' => 4,
                    'name' => \Yii::t('app', 'Подлключаемые'),
                    'grid_business_process_id' => 2,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' => 'connecting',
                                'cr.contract_type_id = 2',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => 'connecting',
                    'color' => '#F49AC1',
                ),
            5 =>
                array(
                    'id' => 5,
                    'name' => \Yii::t('app', 'Техотказ'),
                    'grid_business_process_id' => 2,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' => 'tech_deny',
                                'cr.contract_type_id = 2',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => true,
                    'oldstatus' => 'tech_deny',
                    'color' => '#996666',
                ),
            6 =>
                array(
                    'id' => 6,
                    'name' => \Yii::t('app', 'Отказ'),
                    'grid_business_process_id' => 2,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' => 'deny',
                                'cr.contract_type_id = 2',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => true,
                    'oldstatus' => 'deny',
                    'color' => '#A0A0A0',
                ),
            7 =>
                array(
                    'id' => 7,
                    'name' => \Yii::t('app', 'Мусор'),
                    'grid_business_process_id' => 2,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' =>
                                    array(
                                        'double',
                                        'trash',
                                    ),
                                'cr.contract_type_id = 2',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => true,
                    'oldstatus' => 'trash',
                    'color' => '#a5e934',
                ),
            33 =>
                array(
                    'id' => 33,
                    'name' => \Yii::t('app', 'Заказ магазина'),
                    'grid_business_process_id' => 3,
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'once',
                    'color' => 'silver',
                ),
            16 =>
                array(
                    'id' => 16,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 4,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' =>
                                    array(
                                        'double',
                                        'trash',
                                    ),
                                'cr.contract_type_id = 5',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'once',
                    'color' => 'silver',
                ),
            18 =>
                array(
                    'id' => 18,
                    'name' => \Yii::t('app', 'Мусор и закрытые'),
                    'grid_business_process_id' => 4,
                    'queryParams' => [
                        'where' =>
                            array(
                                'c.status' =>
                                    array(
                                        'double',
                                        'trash',
                                    ),
                                'cr.contract_type_id = 5',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            32 =>
                array(
                    'id' => 32,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 5,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 32',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'distr',
                    'color' => 'yellow',
                ),
            36 =>
                array(
                    'id' => 36,
                    'name' => \Yii::t('app', 'В стадии переговоров'),
                    'grid_business_process_id' => 5,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 36',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'negotiations',
                    'color' => '#C4DF9B',
                ),
            108 =>
                array(
                    'id' => 108,
                    'name' => \Yii::t('app', 'GPON'),
                    'grid_business_process_id' => 6,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 108',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'distr',
                    'color' => '',
                ),
            109 =>
                array(
                    'id' => 109,
                    'name' => \Yii::t('app', 'ВОЛС'),
                    'grid_business_process_id' => 6,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 109',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'distr',
                    'color' => '',
                ),
            110 =>
                array(
                    'id' => 110,
                    'name' => \Yii::t('app', 'Сервисный'),
                    'grid_business_process_id' => 6,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 110',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'distr',
                    'color' => '',
                ),
            15 =>
                array(
                    'id' => 15,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 6,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 15',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'distr',
                    'color' => 'yellow',
                ),
            92 =>
                array(
                    'id' => 92,
                    'name' => \Yii::t('app', 'Закрытый'),
                    'grid_business_process_id' => 6,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 92',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'closed',
                    'color' => '',
                ),
            93 =>
                array(
                    'id' => 93,
                    'name' => \Yii::t('app', 'Самозакупки'),
                    'grid_business_process_id' => 6,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 93',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'distr',
                    'color' => '',
                ),
            94 =>
                array(
                    'id' => 94,
                    'name' => \Yii::t('app', 'Разовый'),
                    'grid_business_process_id' => 6,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 94',
                                'cr.contract_type_id = 4',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'distr',
                    'color' => '',
                ),
            24 =>
                array(
                    'id' => 24,
                    'name' => \Yii::t('app', 'Переговоры'),
                    'grid_business_process_id' => 8,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 24',
                                'cr.contract_type_id = 7',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            35 =>
                array(
                    'id' => 35,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 8,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 35',
                                'cr.contract_type_id = 7',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            26 =>
                array(
                    'id' => 26,
                    'name' => \Yii::t('app', 'Закрытый'),
                    'grid_business_process_id' => 8,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 26',
                                'cr.contract_type_id = 7',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            30 =>
                array(
                    'id' => 30,
                    'name' => \Yii::t('app', 'Входящие'),
                    'grid_business_process_id' => 9,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 1',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'income',
                    'color' => '#CCFFFF',
                ),
            34 =>
                array(
                    'id' => 34,
                    'name' => \Yii::t('app', 'Внутренний офис'),
                    'grid_business_process_id' => 10,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 6',
                                'cr.business_process_status_id = 34',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => true,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            111 =>
                array(
                    'id' => 111,
                    'name' => \Yii::t('app', 'Закрытые'),
                    'grid_business_process_id' => 10,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.contract_type_id = 6',
                                'cr.business_process_status_id = 111',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            37 =>
                array(
                    'id' => 37,
                    'name' => \Yii::t('app', 'Входящий'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 37',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'income',
                    'color' => '#CCFFFF',
                ),
            38 =>
                array(
                    'id' => 38,
                    'name' => \Yii::t('app', 'Переговоры'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 38',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'negotiations',
                    'color' => '#C4DF9B',
                ),
            39 =>
                array(
                    'id' => 39,
                    'name' => \Yii::t('app', 'Тестирование'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 39',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'testing',
                    'color' => '#6DCFF6',
                ),
            40 =>
                array(
                    'id' => 40,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 40',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'work',
                    'color' => '',
                ),
            107 =>
                array(
                    'id' => 107,
                    'name' => \Yii::t('app', 'Ручной счет'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 107',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '#CCFFFF',
                ),
            41 =>
                array(
                    'id' => 41,
                    'name' => \Yii::t('app', 'Приостановлен'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 41',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'suspended',
                    'color' => '#C4a3C0',
                ),
            42 =>
                array(
                    'id' => 42,
                    'name' => \Yii::t('app', 'Расторгнут'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 42',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'closed',
                    'color' => '#FFFFCC',
                ),
            43 =>
                array(
                    'id' => 43,
                    'name' => \Yii::t('app', 'Фрод блокировка'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 43',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'blocked',
                    'color' => 'silver',
                ),
            44 =>
                array(
                    'id' => 44,
                    'name' => \Yii::t('app', 'Техотказ'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 44',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'tech_deny',
                    'color' => '#996666',
                ),
            45 =>
                array(
                    'id' => 45,
                    'name' => \Yii::t('app', 'Автоблокировка'),
                    'grid_business_process_id' => 11,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 45',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            47 =>
                array(
                    'id' => 47,
                    'name' => \Yii::t('app', 'Входящий'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 47',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'income',
                    'color' => '#CCFFFF',
                ),
            48 =>
                array(
                    'id' => 48,
                    'name' => \Yii::t('app', 'Переговоры'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 48',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'negotiations',
                    'color' => '#C4DF9B',
                ),
            49 =>
                array(
                    'id' => 49,
                    'name' => \Yii::t('app', 'Тестирование'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 49',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'testing',
                    'color' => '#6DCFF6',
                ),
            50 =>
                array(
                    'id' => 50,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 50',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'work',
                    'color' => '',
                ),
            51 =>
                array(
                    'id' => 51,
                    'name' => \Yii::t('app', 'Приостановлен'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 51',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'suspended',
                    'color' => '#C4a3C0',
                ),
            52 =>
                array(
                    'id' => 52,
                    'name' => \Yii::t('app', 'Расторгнут'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 52',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'closed',
                    'color' => '#FFFFCC',
                ),
            53 =>
                array(
                    'id' => 53,
                    'name' => \Yii::t('app', 'Фрод блокировка'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 53',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'blocked',
                    'color' => 'silver',
                ),
            54 =>
                array(
                    'id' => 54,
                    'name' => \Yii::t('app', 'Техотказ'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 54',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'tech_deny',
                    'color' => '#996666',
                ),
            55 =>
                array(
                    'id' => 55,
                    'name' => \Yii::t('app', 'Автоблокировка'),
                    'grid_business_process_id' => 12,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            62 =>
                array(
                    'id' => 62,
                    'name' => \Yii::t('app', 'Входящий'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 62',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'income',
                    'color' => '#CCFFFF',
                ),
            63 =>
                array(
                    'id' => 63,
                    'name' => \Yii::t('app', 'Переговоры'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 63',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'negotiations',
                    'color' => '#C4DF9B',
                ),
            64 =>
                array(
                    'id' => 64,
                    'name' => \Yii::t('app', 'Тестирование'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 64',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'testing',
                    'color' => '#6DCFF6',
                ),
            65 =>
                array(
                    'id' => 65,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 65',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'work',
                    'color' => '',
                ),
            66 =>
                array(
                    'id' => 66,
                    'name' => \Yii::t('app', 'Приостановлен'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 66',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'suspended',
                    'color' => '#C4a3C0',
                ),
            67 =>
                array(
                    'id' => 67,
                    'name' => \Yii::t('app', 'Расторгнут'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 67',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'closed',
                    'color' => '#FFFFCC',
                ),
            68 =>
                array(
                    'id' => 68,
                    'name' => \Yii::t('app', 'Фрод блокировка'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 68',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'blocked',
                    'color' => 'silver',
                ),
            69 =>
                array(
                    'id' => 69,
                    'name' => \Yii::t('app', 'Техотказ'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 69',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'tech_deny',
                    'color' => '#996666',
                ),
            70 =>
                array(
                    'id' => 70,
                    'name' => \Yii::t('app', 'Автоблокировка'),
                    'grid_business_process_id' => 13,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            77 =>
                array(
                    'id' => 77,
                    'name' => \Yii::t('app', 'Входящий'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 77',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'income',
                    'color' => '#CCFFFF',
                ),
            78 =>
                array(
                    'id' => 78,
                    'name' => \Yii::t('app', 'Переговоры'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 78',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'negotiations',
                    'color' => '#C4DF9B',
                ),
            79 =>
                array(
                    'id' => 79,
                    'name' => \Yii::t('app', 'Тестирование'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 79',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'testing',
                    'color' => '#6DCFF6',
                ),
            80 =>
                array(
                    'id' => 80,
                    'name' => \Yii::t('app', 'Действующий'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 80',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'work',
                    'color' => '',
                ),
            81 =>
                array(
                    'id' => 81,
                    'name' => \Yii::t('app', 'Приостановлен'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 81',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'suspended',
                    'color' => '#C4a3C0',
                ),
            82 =>
                array(
                    'id' => 82,
                    'name' => \Yii::t('app', 'Расторгнут'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 82',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'closed',
                    'color' => '#FFFFCC',
                ),
            83 =>
                array(
                    'id' => 83,
                    'name' => \Yii::t('app', 'Фрод блокировка'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 83',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'blocked',
                    'color' => 'silver',
                ),
            84 =>
                array(
                    'id' => 84,
                    'name' => \Yii::t('app', 'Техотказ'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                'cr.business_process_status_id = 84',
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'tech_deny',
                    'color' => '#996666',
                ),
            85 =>
                array(
                    'id' => 85,
                    'name' => \Yii::t('app', 'Автоблокировка'),
                    'grid_business_process_id' => 14,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 3',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            95 =>
                array(
                    'id' => 95,
                    'name' => \Yii::t('app', 'Пуско-наладка'),
                    'grid_business_process_id' => 15,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 95',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'connecting',
                    'color' => '',
                ),
            96 =>
                array(
                    'id' => 96,
                    'name' => \Yii::t('app', 'Техобслуживание'),
                    'grid_business_process_id' => 15,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 96',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'work',
                    'color' => '',
                ),
            97 =>
                array(
                    'id' => 97,
                    'name' => \Yii::t('app', 'Без Техобслуживания'),
                    'grid_business_process_id' => 15,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 97',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'work',
                    'color' => '',
                ),
            98 =>
                array(
                    'id' => 98,
                    'name' => \Yii::t('app', 'Приостановленные'),
                    'grid_business_process_id' => 15,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 98',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'suspended',
                    'color' => '',
                ),
            99 =>
                array(
                    'id' => 99,
                    'name' => \Yii::t('app', 'Отказ'),
                    'grid_business_process_id' => 15,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 99',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => false,
                    'oldstatus' => 'deny',
                    'color' => '',
                ),
            100 =>
                array(
                    'id' => 100,
                    'name' => \Yii::t('app', 'Мусор'),
                    'grid_business_process_id' => 15,
                    'queryParams' => [
                        'where' =>
                            array(
                                0 =>
                                    array(
                                        '!=',
                                        'cr.business_process_status_id',
                                        2 => 11,
                                    ),
                                'cr.contract_type_id = 100',
                            ),
                    ],
                    'columns' => [
                        'status',
                        'id',
                        'company',
                        'created',
                        'currency',
                        'sale_channel',
                        'manager',
                        'region',
                    ],
                    'default' => false,
                    'show_as_status' => true,
                    'is_close_status' => true,
                    'oldstatus' => 'trash',
                    'color' => '',
                ),
            101 =>
                array(
                    'id' => 101,
                    'name' => \Yii::t('app', 'Доход по клиентам'),
                    'grid_business_process_id' => 16,
                    'queryParams' => [
                        'select' =>
                            array(
                                'l.service',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
                                'sum(l.sum) as total',
                                'b.bill_date',
                            ),
                        'join' =>
                            array(
                                0 =>
                                    array(
                                        'INNER JOIN',
                                        'newbills b',
                                        'c.id=b.client_id',
                                    ),
                                1 =>
                                    array(
                                        'INNER JOIN',
                                        'newbill_lines l',
                                        'l.bill_no=b.bill_no',
                                    ),
                            ),
                        'where' =>
                            array(
                                'b.is_payed = 1',
                                'l.type = "service"',
                                array(
                                    'not in',
                                    'l.service',
                                    2 =>
                                        array(
                                            '1C',
                                            'bill_monthlyadd',
                                            '',
                                            'all4net',
                                        ),
                                ),
                                ' b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to'
                            ),
                        'groupBy' =>
                            array(
                                'l.service',
                                'c.id',
                            ),
                        'orderBy' =>
                            array(
                                'over' => 3,
                            ),
                        'params' => [
                            'date_from' => date('Y-m-01'),
                            'date_to' => date('Y-m-t')
                        ]
                    ],
                    'columns' => [
                        'id',
                        'company',
                        'account_manager',
                        'manager',
                        'region',
                        'service',
                        'bill_date',
                        'abon',
                        'over',
                        'total',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            102 =>
                array(
                    'id' => 102,
                    'name' => \Yii::t('app', 'Доход по менеджеру и услугам'),
                    'grid_business_process_id' => 16,
                    'queryParams' => [
                        'select' =>
                            array(
                                'l.service',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
                                'sum(l.sum) as total',
                                'b.bill_date',
                            ),
                        'join' =>
                            array(
                                0 =>
                                    array(
                                        'INNER JOIN',
                                        'newbills b',
                                        'c.id=b.client_id',
                                    ),
                                1 =>
                                    array(
                                        'INNER JOIN',
                                        'newbill_lines l',
                                        'l.bill_no=b.bill_no',
                                    ),
                            ),
                        'where' =>
                            array(
                                'b.is_payed = 1',
                                'l.type = "service"',
                                array(
                                    'not in',
                                    'l.service',
                                    2 =>
                                        array(
                                            '1C',
                                            'bill_monthlyadd',
                                            '',
                                            'all4net',
                                        ),
                                ),
                                ' b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to'
                            ),
                        'groupBy' =>
                            array(
                                'cr.account_manager',
                            ),
                        'orderBy' =>
                            array(
                                'over' => 3,
                            ),
                        'params' => [
                            'date_from' => date('Y-m-01'),
                            'date_to' => date('Y-m-t')
                        ]
                    ],
                    'columns' => [
                        'account_manager',
                        'service',
                        'region',
                        'abon',
                        'over',
                        'total',
                        'bill_date',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            103 =>
                array(
                    'id' => 103,
                    'name' => \Yii::t('app', 'Доход по менеджеру'),
                    'grid_business_process_id' => 16,
                    'queryParams' => [
                        'select' =>
                            array(
                                'l.service',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
                                'sum(l.sum) as total',
                                'b.bill_date',
                            ),
                        'join' =>
                            array(
                                0 =>
                                    array(
                                        'INNER JOIN',
                                        'newbills b',
                                        'c.id=b.client_id',
                                    ),
                                1 =>
                                    array(
                                        'INNER JOIN',
                                        'newbill_lines l',
                                        'l.bill_no=b.bill_no',
                                    ),
                            ),
                        'where' =>
                            array(
                                'b.is_payed = 1',
                                'l.type = "service"',
                                array(
                                    'not in',
                                    'l.service',
                                    2 =>
                                        array(
                                            '1C',
                                            'bill_monthlyadd',
                                            '',
                                            'all4net',
                                        ),
                                ),
                                ' b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to'
                            ),
                        'groupBy' =>
                            array(
                                'cr.account_manager',
                            ),
                        'orderBy' =>
                            array(
                                'over' => 3,
                            ),
                        'params' => [
                            'date_from' => date('Y-m-01'),
                            'date_to' => date('Y-m-t')
                        ]
                    ],
                    'columns' => [
                        'account_manager',
                        'region',
                        'abon',
                        'over',
                        'total',
                        'bill_date',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            104 =>
                array(
                    'id' => 104,
                    'name' => \Yii::t('app', 'Доход по услугам'),
                    'grid_business_process_id' => 16,
                    'queryParams' => [
                        'select' =>
                            array(
                                'l.service',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
                                'sum(l.sum) as total',
                                'b.bill_date',
                            ),
                        'join' =>
                            array(
                                0 =>
                                    array(
                                        'INNER JOIN',
                                        'newbills b',
                                        'c.id=b.client_id',
                                    ),
                                1 =>
                                    array(
                                        'INNER JOIN',
                                        'newbill_lines l',
                                        'l.bill_no=b.bill_no',
                                    ),
                            ),
                        'where' =>
                            array(
                                'b.is_payed = 1',
                                'l.type = "service"',
                                array(
                                    'not in',
                                    'l.service',
                                    2 =>
                                        array(
                                            '1C',
                                            'bill_monthlyadd',
                                            '',
                                            'all4net',
                                        ),
                                ),
                                'b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to'
                            ),
                        'groupBy' =>
                            array(
                                'l.service',
                            ),
                        'orderBy' =>
                            array(
                                'over' => 3,
                            ),
                        'params' => [
                            'date_from' => date('Y-m-01'),
                            'date_to' => date('Y-m-t')
                        ]
                    ],
                    'columns' => [
                        'service',
                        'region',
                        'abon',
                        'over',
                        'total',
                        'bill_date',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
            106 =>
                array(
                    'id' => 106,
                    'name' => \Yii::t('app', 'Расхождение'),
                    'grid_business_process_id' => 16,
                    'queryParams' => [
                        'select' =>
                            array(
                                'l.service',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND DATE_ADD( :date_to, INTERVAL -1 MONTH),l.sum,0)) AS abon',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN  :date_from AND  :date_to,l.sum,0)) AS abon1',
                                'SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND DATE_ADD( :date_to, INTERVAL -1 MONTH),l.sum,0)) AS over',
                                'SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN  :date_from AND  :date_to,l.sum,0)) AS over1',
                                'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN  :date_from AND  :date_to,l.sum,0))-SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0 AND b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND DATE_ADD( :date_to, INTERVAL -1 MONTH),l.sum,0)) AS abondiff',
                                'SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN  :date_from AND  :date_to,l.sum,0))-SUM(IF((MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11) AND b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND DATE_ADD( :date_to, INTERVAL -1 MONTH),l.sum,0)) As overdiff',
                                'b.bill_date',
                            ),
                        'join' =>
                            array(
                                0 =>
                                    array(
                                        'INNER JOIN',
                                        'newbills b',
                                        'c.id=b.client_id',
                                    ),
                                1 =>
                                    array(
                                        'INNER JOIN',
                                        'newbill_lines l',
                                        'l.bill_no=b.bill_no',
                                    ),
                            ),
                        'where' =>
                            array(
                                'l.type = "service"',
                                array(
                                    'not in',
                                    'l.service',
                                    2 =>
                                        array(
                                            '1C',
                                            'bill_monthlyadd',
                                            '',
                                            'all4net',
                                        ),
                                ),
                                ' b.bill_date BETWEEN DATE_ADD( :date_from, INTERVAL -1 MONTH) AND  :date_to'
                            ),
                        'groupBy' =>
                            array(
                                'l.service',
                                'c.id',
                            ),
                        'orderBy' =>
                            array(
                                'over' => 3,
                            ),
                        'params' => [
                            'date_from' => date('Y-m-01'),
                            'date_to' => date('Y-m-t')
                        ]
                    ],
                    'columns' => [
                        'id',
                        'company',
                        'account_manager',
                        'manager',
                        'region',
                        'service',
                        'bill_date',
                        'abon',
                        'abon1',
                        'over',
                        'over1',
                        'abondiff',
                        'overdiff',
                    ],
                    'default' => false,
                    'show_as_status' => false,
                    'is_close_status' => false,
                    'oldstatus' => NULL,
                    'color' => '',
                ),
        ]];