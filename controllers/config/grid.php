<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$menumap = [
    'telecom' => [
        'sales' => [
            'testing',
            'connecting',
        ],
        'accounting' => [
            'testing',
            'connecting',
            'work',
            'debt',
        ],
    ],
    'ecommerce' => [
        'sales' => [
            'testing',
        ],
        'accounting' => [
            'work',
        ],
    ], 
];

$rows['testing'] = [
    'header' => 'Тестируемые',
    'sql' => 'SELECT 
				cl.status, cl.id, cl.client, cl.company, cl.manager, cl.support, cl.telemarketing, s.name AS sale_channel, cl.created, cl.currency, 
				DATE(cls.ts) date_zayavka
			FROM clients cl
			LEFT JOIN client_statuses cls ON cl.id = cls.id_client
			AND
				( cls.id IS NULL AND
					cls.id = (SELECT MIN(id) FROM client_statuses WHERE id_client=cl.id)
				)
                        LEFT JOIN sale_channels s ON (s.id = cl.sale_channel)
			WHERE
				1 AND cl.status="testing"',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'created' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'status' => [
                'label' => '#',
                'class' => 'app\classes\yii\GlyphDataColumn',
             ],
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'client' => [
                'label' => 'Клиент',
             ],
            'company' => [
                'label' => 'Компания',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'created' => [
                'label' => 'Заведен',
             ],
            'curency' => [
                'label' => 'Валюта',    
             ],
            'sale_channel' => [
                'label' => 'Канал',
             ],
            'manager' => [
                'label' => 'Менеджер',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',
             ],
            'support' => [
                'label' => 'ТП',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',

             ],
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['connecting'] = [
    'header' => 'Подключаемые',
    'sql' => 'SELECT 
				cl.status, cl.id, cl.client, cl.company, cl.manager, cl.support, cl.telemarketing, s.name AS sale_channel, cl.created, cl.currency, 
				DATE(cls.ts) date_zayavka
			FROM clients cl
			LEFT JOIN client_statuses cls ON cl.id = cls.id_client
			AND
				( cls.id IS NULL AND
					cls.id = (SELECT MIN(id) FROM client_statuses WHERE id_client=cl.id)
				)
                        LEFT JOIN sale_channels s ON (s.id = cl.sale_channel)        
			WHERE
				1 AND cl.status="connecting"',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'created' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'status' => [
                'label' => '#',
                'class' => 'app\classes\yii\GlyphDataColumn',
             ],
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'client' => [
                'label' => 'Клиент',
             ],
            'company' => [
                'label' => 'Компания',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'created' => [
                'label' => 'Заведен',
             ],
            'curency' => [
                'label' => 'Валюта',    
             ],
            'sale_channel' => [
                'label' => 'Канал',
             ],
            'manager' => [
                'label' => 'Менеджер',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',
             ],
            'support' => [
                'label' => 'ТП',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',

             ],
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['work'] = [
    'header' => 'Включенные',
    'sql' => 'SELECT 
				cl.status, cl.id, cl.client, cl.company, cl.manager, cl.support, cl.telemarketing, s.name AS sale_channel, cl.created, cl.currency, 
				DATE(cls.ts) date_zayavka
			FROM clients cl
			LEFT JOIN client_statuses cls ON cl.id = cls.id_client
			AND
				( cls.id IS NULL AND
					cls.id = (SELECT MIN(id) FROM client_statuses WHERE id_client=cl.id)
				)
                        LEFT JOIN sale_channels s ON (s.id = cl.sale_channel)        
			WHERE
				1 AND cl.status="work"',
    'sortable' => [
           /* 'status',*/
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'created' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
           /* 'status' => [
                'label' => '#',
                'class' => 'app\classes\yii\GlyphDataColumn',
             ],*/
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'client' => [
                'label' => 'Клиент',
             ],
            'company' => [
                'label' => 'Компания',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'created' => [
                'label' => 'Заведен',
             ],
            'curency' => [
                'label' => 'Валюта',    
             ],
            'sale_channel' => [
                'label' => 'Канал',
             ],
            'manager' => [
                'label' => 'Менеджер',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',
             ],
             /*'support' => [
                'label' => 'ТП',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',

             ],*/
            /*'telemarketing' => [
                'label' => 'ТМ',    
             ],*/
    ],
];

$rows['debt'] = [
    'header' => 'Отключенные за долги',
    'sql' => 'SELECT 
				cl.status, cl.id, cl.client, cl.company, cl.manager, cl.support, cl.telemarketing, s.name AS sale_channel, cl.created, cl.currency, 
				DATE(cls.ts) date_zayavka
			FROM clients cl
			LEFT JOIN client_statuses cls ON cl.id = cls.id_client
			AND
				( cls.id IS NULL AND
					cls.id = (SELECT MIN(id) FROM client_statuses WHERE id_client=cl.id)
				)
                        LEFT JOIN sale_channels s ON (s.id = cl.sale_channel)        
			WHERE
				1 AND cl.status="debt"',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'created' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'status' => [
                'label' => '#',
                'class' => 'app\classes\yii\GlyphDataColumn',
             ],
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'client' => [
                'label' => 'Клиент',
             ],
            'company' => [
                'label' => 'Компания',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={id}',
             ],
            'created' => [
                'label' => 'Заведен',
             ],
            'curency' => [
                'label' => 'Валюта',    
             ],
            'sale_channel' => [
                'label' => 'Канал',
             ],
            'manager' => [
                'label' => 'Менеджер',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',
             ],
            'support' => [
                'label' => 'ТП',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=users&m=user&id={manager}',

             ],
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

