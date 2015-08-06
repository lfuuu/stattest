<?php

use app\models\ContractType;
use app\models\ClientBPStatuses;
use app\models\ClientGridBussinesProcess;

return [

    24 => [
        'id' => 24,
        'name' => \Yii::t('app', 'Переговоры'),
        'grid_business_process_id' => ClientGridBussinesProcess::PARTNER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PARTNER_MAINTENANCE_NEGOTIATIONS],
                ['cr.contract_type_id' => ContractType::PARTNER],
            ],
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
        'oldstatus' => null,
        'color' => '',
    ],
    35 => [
        'id' => 35,
        'name' => \Yii::t('app', 'Действующий'),
        'grid_business_process_id' => ClientGridBussinesProcess::PARTNER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PARTNER_MAINTENANCE_ACTING],
                ['cr.contract_type_id' => ContractType::PARTNER],
            ],
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
        'oldstatus' => null,
        'color' => '',
    ],
    26 => [
        'id' => 26,
        'name' => \Yii::t('app', 'Закрытый'),
        'grid_business_process_id' => ClientGridBussinesProcess::PARTNER_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['cr.business_process_status_id' => ClientBPStatuses::PARTNER_MAINTENANCE_CLOSED],
                ['cr.contract_type_id' => ContractType::PARTNER],
            ],
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
        'oldstatus' => null,
        'color' => '',
    ],

];