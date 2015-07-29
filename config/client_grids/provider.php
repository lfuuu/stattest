<?php

use app\models\ClientContractType;
use app\models\ClientBPStatuses;
use app\models\ClientGridBussinesProcess;
use app\models\User;

$provider_manager_column = [
    'filter' => function () {
        return \kartik\widgets\Select2::widget([
            'name' => 'manager',
            'data' => User::getUserListByDepart(User::DEPART_PURCHASE, ['enabled' => 'yes', 'primary' => 'user']),
            'value' => \Yii::$app->request->get('manager'),
            'options' => ['placeholder' => 'Начните вводить фамилию'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    },
];

return [

    32 => [
        'id' => 32,
        'name' => \Yii::t('app', 'Действующий'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_ORDERS,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_ORDERS_ACTING],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'distr',
        'color' => 'yellow',
    ],
    36 => [
        'id' => 36,
        'name' => \Yii::t('app', 'В стадии переговоров'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_ORDERS,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_ORDERS_NEGOTIATION_STAGE],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'negotiations',
        'color' => '#C4DF9B',
    ],
    108 => [
        'id' => 108,
        'name' => \Yii::t('app', 'GPON'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_GPON],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'distr',
        'color' => '',
    ],
    109 => [
        'id' => 109,
        'name' => \Yii::t('app', 'ВОЛС'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_VOLS],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'distr',
        'color' => '',
    ],
    110 => [
        'id' => 110,
        'name' => \Yii::t('app', 'Сервисный'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_SERVICE],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'distr',
        'color' => '',
    ],
    15 => [
        'id' => 15,
        'name' => \Yii::t('app', 'Действующий'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_ACTING],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'distr',
        'color' => 'yellow',
    ],
    92 => [
        'id' => 92,
        'name' => \Yii::t('app', 'Закрытый'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_CLOSED],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => true,
        'oldstatus' => 'closed',
        'color' => '',
    ],
    93 => [
        'id' => 93,
        'name' => \Yii::t('app', 'Самозакупки'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_SELF_BUY],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'distr',
        'color' => '',
    ],
    94 => [
        'id' => 94,
        'name' => \Yii::t('app', 'Разовый'),
        'grid_business_process_id' => ClientGridBussinesProcess::PROVIDER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PROVIDER_MAINTENANCE_ONCE],
                ['cr.contract_type_id' => ClientContractType::PROVIDER],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $provider_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => 'distr',
        'color' => '',
    ],

];