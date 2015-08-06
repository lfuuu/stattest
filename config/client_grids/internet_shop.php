<?php

use app\models\ContractType;
use app\models\ClientBPStatuses;
use app\models\ClientGridBussinesProcess;
use app\models\User;

$internet_shop_manager_column = [
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

    16 => [
        'id' => 16,
        'name' => \Yii::t('app', 'Действующий'),
        'grid_business_process_id' => ClientGridBussinesProcess::INTERNET_SHOP_MAINTENANCE,
        'queryParams' => [
            'where' => [
                ['not in', 'c.status', ['double', 'trash']],
                ['cr.contract_type_id' => ContractType::INTERNET_SHOP],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $internet_shop_manager_column,
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
                ['cr.contract_type_id' => ContractType::INTERNET_SHOP],
            ],
        ],
        'columns' => [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager' => $internet_shop_manager_column,
            'region',
        ],
        'default' => false,
        'show_as_status' => false,
        'is_close_status' => false,
        'oldstatus' => null,
        'color' => '',
    ],

];