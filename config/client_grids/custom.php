<?php

use app\models\ContractType;
use app\models\ClientBPStatuses;
use app\models\ClientGridBussinesProcess;

return [

    33 => [
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
    ],
    30 => [
        'id' => 30,
        'name' => \Yii::t('app', 'Входящие'),
        'grid_business_process_id' => 9,
        'queryParams' => [
            'where' => [
                'cr.contract_type_id = 1',
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
        'oldstatus' => 'income',
        'color' => '#CCFFFF',
    ],

];