<?php

use app\models\ClientContractType;
use app\models\ClientBPStatuses;
use app\models\ClientGridBussinesProcess;

return [

    16 => [
        'id' => 16,
        'name' => \Yii::t('app', 'Действующий'),
        'grid_business_process_id' => ClientGridBussinesProcess::INTERNET_SHOP_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['in', 'c.status', ['double', 'trash']],
                ['cr.contract_type_id' => ClientContractType::INTERNET_SHOP],
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
        'oldstatus' => 'once',
        'color' => 'silver',
    ],
    18 => [
        'id' => 18,
        'name' => \Yii::t('app', 'Мусор и закрытые'),
        'grid_business_process_id' => 4,
        'queryParams' => [
            'where' => [
                ['in', 'c.status', ['double', 'trash']],
                ['cr.contract_type_id' => ClientContractType::INTERNET_SHOP],
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
        'oldstatus' => NULL,
        'color' => '',
    ],

];