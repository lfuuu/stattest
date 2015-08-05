<?php

use app\models\ContractType;
use app\models\ClientBPStatuses;
use app\models\ClientGridBussinesProcess;

return [

    34 => [
        'id' => 34,
        'name' => \Yii::t('app', 'Внутренний офис'),
        'grid_business_process_id' => ClientGridBussinesProcess::INTERNAL_OFFICE,
        'queryParams' => [
            'where' => [
                ['cr.contract_type_id' => ContractType::INTERNAL_OFFICE],
                ['cr.business_process_status_id' => ClientBPStatuses::INTERNAL_OFFICE],
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
        'default' => true,
        'show_as_status' => true,
        'is_close_status' => false,
        'oldstatus' => null,
        'color' => '',
    ],
    111 => [
        'id' => 111,
        'name' => \Yii::t('app', 'Закрытые'),
        'grid_business_process_id' => ClientGridBussinesProcess::INTERNAL_OFFICE,
        'queryParams' => [
            'where' => [
                ['cr.contract_type_id' => ContractType::INTERNAL_OFFICE],
                ['cr.business_process_status_id' => ClientBPStatuses::INTERNAL_OFFICE_CLOSED],
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

];