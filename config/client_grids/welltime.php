<?php

use app\models\ContractSubdivision;
use app\models\BusinessProcessStatus;
use app\models\BusinessProcess;

return [

    95 => [
        'id' => 95,
        'name' => \Yii::t('app', 'Пуско-наладка'),
        'grid_business_process_id' => BusinessProcess::WELLTIME_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['!=', 'cr.business_process_status_id', BusinessProcessStatus::STATE_NEGOTIATIONS,],
                ['cr.contract_subdivision_id' => 95],
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
        'oldstatus' => 'connecting',
        'color' => '',
    ],
    96 => [
        'id' => 96,
        'name' => \Yii::t('app', 'Техобслуживание'),
        'grid_business_process_id' => BusinessProcess::WELLTIME_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['!=', 'cr.business_process_status_id', BusinessProcessStatus::STATE_NEGOTIATIONS,],
                ['cr.contract_subdivision_id' => 96],
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
        'oldstatus' => 'work',
        'color' => '',
    ],
    97 => [
        'id' => 97,
        'name' => \Yii::t('app', 'Без Техобслуживания'),
        'grid_business_process_id' => BusinessProcess::WELLTIME_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['!=', 'cr.business_process_status_id', BusinessProcessStatus::STATE_NEGOTIATIONS,],
                ['cr.contract_subdivision_id' => 97],
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
        'oldstatus' => 'work',
        'color' => '',
    ],
    98 => [
        'id' => 98,
        'name' => \Yii::t('app', 'Приостановленные'),
        'grid_business_process_id' => BusinessProcess::WELLTIME_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['!=', 'cr.business_process_status_id', BusinessProcessStatus::STATE_NEGOTIATIONS,],
                ['cr.contract_subdivision_id' => 98],
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
        'oldstatus' => 'suspended',
        'color' => '',
    ],
    99 => [
        'id' => 99,
        'name' => \Yii::t('app', 'Отказ'),
        'grid_business_process_id' => BusinessProcess::WELLTIME_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['!=', 'cr.business_process_status_id', BusinessProcessStatus::STATE_NEGOTIATIONS,],
                ['cr.contract_subdivision_id' => 99],
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
        'oldstatus' => 'deny',
        'color' => '',
    ],
    100 => [
        'id' => 100,
        'name' => \Yii::t('app', 'Мусор'),
        'grid_business_process_id' => BusinessProcess::WELLTIME_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['!=', 'cr.business_process_status_id', BusinessProcessStatus::STATE_NEGOTIATIONS,],
                ['cr.contract_subdivision_id' => 100],
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
        'is_close_status' => true,
        'oldstatus' => 'trash',
        'color' => '',
    ],

];