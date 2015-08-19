<?php

use app\models\ContractSubdivision;
use app\models\BusinessProcessStatus;
use app\models\BusinessProcess;

return [

    34 => [
        'id' => 34,
        'name' => \Yii::t('app', 'Внутренний офис'),
        'grid_business_process_id' => BusinessProcess::INTERNAL_OFFICE,
        'queryParams' => [
            'where' => [
                ['cr.contract_subdivision_id' => ContractSubdivision::INTERNAL_OFFICE],
                ['cr.business_process_status_id' => BusinessProcessStatus::INTERNAL_OFFICE],
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
        'grid_business_process_id' => BusinessProcess::INTERNAL_OFFICE,
        'queryParams' => [
            'where' => [
                ['cr.contract_subdivision_id' => ContractSubdivision::INTERNAL_OFFICE],
                ['cr.business_process_status_id' => BusinessProcessStatus::INTERNAL_OFFICE_CLOSED],
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