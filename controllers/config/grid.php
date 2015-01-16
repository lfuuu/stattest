<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$menumap = [
    'telecom' => [
        'sales' => [
            'telecom.sales.income',
            'telecom.sales.negotiations',
            'telecom.sales.testing',
            'telecom.sales.connecting',
            'telecom.sales.tech_deny',
            'telecom.sales.deny',
            'telecom.sales.trash',
        ],
        'accounting' => [
            'telecom.sales.connecting',
            'telecom.accounting.work',
            'telecom.accounting.closed', // отключен
            'telecom.accounting.debt', // отключен за долги
            'telecom.accounting.blocked', //временно заблокирован
            'telecom.accounting.reserved',
            'telecom.accounting.suspended', //приостановлен

        ],
    ],
    'ecommerce' => [
        'sales' => [
            'ecommerce.sales.all',
            'ecommerce.sales.new',
            'ecommerce.sales.wimax',
            'ecommerce.sales.netbynet',
            'ecommerce.sales.onlime',
            'ecommerce.sales.mts',
            'ecommerce.sales.postponed',
            'ecommerce.sales.reserved',
            'ecommerce.sales.confirm',
            'ecommerce.sales.delivery',
            'ecommerce.sales.forshipment',
            'ecommerce.sales.shipped',
            'ecommerce.sales.ontheway',
            'ecommerce.sales.activation',
            'ecommerce.sales.closed',
            'ecommerce.sales.deny',
        ],
    ],
    'procurement' => [
        'sales' => [
            'procurement.sales.new',
            'procurement.sales.payment',
            'procurement.sales.delivery',
            'procurement.sales.arrived',
            'procurement.sales.closed',
            'procurement.sales.deny',
        ],
        'accounting' => [
            'procurement.accounting.work',
        ],
    ], 
    'operator' => [
        'accounting' => [
            'operators.accounting.work',
            'operators.accounting.deny',
            'operators.accounting.testing',
        ],
    ], 
];

$rows['telecom.sales.income'] = [
    'header' => 'Входящие',
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
				cl.contract_type_id=2 AND cl.status="income"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['telecom.sales.negotiations'] = [
    'header' => 'В стадии переговоров',
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
				cl.contract_type_id=2 AND cl.status="negotiations"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['telecom.sales.testing'] = [
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
				cl.contract_type_id=2 AND cl.status="testing"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['telecom.sales.connecting'] = [
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
				cl.contract_type_id=2 AND cl.status="connecting"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['telecom.sales.tech_deny'] = [
    'header' => 'Техотказ',
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
				cl.contract_type_id=2 AND cl.status="tech_deny"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['telecom.sales.deny'] = [
    'header' => 'Отказ',
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
				cl.contract_type_id=2 AND cl.status="deny"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['telecom.sales.trash'] = [
    'header' => 'Мусор',
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
				cl.contract_type_id=2 AND cl.status in ("trash","double")',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];





























/*---------СОПРОВОЖДЕНИЕ-------------*/

$rows['telecom.accounting.work'] = [
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
				cl.contract_type_id=2 AND cl.status="work"',
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
    ],
];

$rows['telecom.accounting.closed'] = [
    'header' => 'Отключенные',
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
				cl.contract_type_id=2 AND cl.status="closed"',
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
    ],
];

$rows['telecom.accounting.debt'] = [
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
				cl.contract_type_id=2 AND cl.status="debt"',
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
    ],
];

$rows['telecom.accounting.blocked'] = [
    'header' => 'Временно заблокированные',
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
				cl.contract_type_id=2 AND cl.status="blocked"',
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
    ],
];

$rows['telecom.accounting.reserved'] = [
    'header' => 'Резервирование канала',
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
				cl.contract_type_id=2 AND cl.status="reserved"',
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
    ],
];

$rows['telecom.accounting.suspended'] = [
    'header' => 'Приостановлен',
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
				cl.contract_type_id=2 AND cl.status="suspended"',
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
    ],
];
































































/*-------------------------------------------------------------------------------------*/

$rows['operators.accounting.work'] = [
    'header' => 'Временно заблокирован',
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
				cl.contract_type_id=3 AND cl.status="work"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['operators.accounting.deny'] = [
    'header' => 'Отказ',
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
				cl.contract_type_id=3 AND cl.status="deny"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['operators.accounting.testing'] = [
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
				cl.contract_type_id=3 AND cl.status="testing"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];


$rows['procurement.sales.new'] = [
    'header' => 'Новый',
    'sql' => '           SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_QUERY,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "incomegoods") AND (T.folder&2147483648))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'id' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=tt&action=view&id={id}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Ответ',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'Пользователь' => [
                'user_main' => 'Пользователь',    
             ],
    ],
];

$rows['procurement.sales.payment'] = [
    'header' => 'Оплата',
    'sql' => 'SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "incomegoods") AND (T.folder&4294967296))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'id' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=tt&action=view&id={id}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Ответ',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'Пользователь' => [
                'user_main' => 'Пользователь',    
             ],
    ],
];

$rows['procurement.sales.delivery'] = [
    'header' => 'Доставка',
    'sql' => 'SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS client_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "incomegoods") AND (T.folder&8589934592))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'id' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=tt&action=view&id={id}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Ответ',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'Пользователь' => [
                'user_main' => 'Пользователь',    
             ],
    ],
];

$rows['procurement.sales.arrived'] = [
    'header' => 'Поступление',
    'sql' => 'SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS client_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "incomegoods") AND (T.folder&17179869184))
            GROUP BY T.id',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'id' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=tt&action=view&id={id}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Ответ',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'Пользователь' => [
                'user_main' => 'Пользователь',    
             ],
    ],
];

$rows['procurement.sales.closed'] = [
    'header' => 'Закрыт',
    'sql' => '           
            SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS client_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id  
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "incomegoods") AND (T.folder&34359738368))
            GROUP BY T.id
            ORDER BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'id' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=tt&action=view&id={id}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Ответ',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'Пользователь' => [
                'user_main' => 'Пользователь',    
             ],
    ],
];

$rows['procurement.sales.deny'] = [
    'header' => 'Отказ',
    'sql' => '           SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS client_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "incomegoods") AND (T.folder&68719476736))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'id' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'id' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=tt&action=view&id={id}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Ответ',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'Пользователь' => [
                'user_main' => 'Пользователь',    
             ],
    ],
];

$rows['procurement.accounting.work'] = [
    'header' => 'Поставщики',
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
				cl.contract_type_id=4 AND cl.status="work"',
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
            'telemarketing' => [
                'label' => 'ТМ',    
             ],
    ],
];

$rows['ecommerce.sales.all'] = [
    'header' => 'Все',
    'sql' => ' SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&1))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.new'] = [
    'header' => 'Новые',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&2))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.wimax'] = [
    'header' => 'WiMax',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&33554432))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.netbynet'] = [
    'header' => 'Net by Net',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&268435456))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.onlime'] = [
    'header' => 'On Lime',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&536870912))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.mts'] = [
    'header' => 'МТС',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&1073741824))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.postponed'] = [
    'header' => 'Отложен',
    'sql' => ' 
        
SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&2097152))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.reserved'] = [
    'header' => 'Зарезервирован',
    'sql' => ' 
        
SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&4))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.confirm'] = [
    'header' => 'Подтвержден',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&4194304))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.delivery'] = [
    'header' => 'Доставка',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&32))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.forshipment'] = [
    'header' => 'К отгрузке',
    'sql' => '
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&8))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.shipped'] = [
    'header' => 'Отгружен',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&16))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.ontheway'] = [
    'header' => 'Выезд',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&8192))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.activation'] = [
    'header' => 'Активация',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&16777216))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.closed'] = [
    'header' => 'Закрыт',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&64))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];

$rows['ecommerce.sales.deny'] = [
    'header' => 'Отказ',
    'sql' => ' 
        SELECT 
                T.*,
                S.*,
                T.client AS client_orig,
                (SELECT COUNT(1) FROM  newbill_sms bs WHERE  T.bill_no = bs.bill_no) is_sms_send,
                T.client AS trouble_original_client,
IF(is_rollback IS NULL OR (is_rollback IS NOT NULL AND !is_rollback), tts.name, ttsrb.name) AS state_name,
                tts.order AS state_order,
                IF(S2.stage_id IS NULL,0,1) AS is_editableByMe,
                IF(S.date_start<=NOW(),1,0) AS is_active,
                UNIX_TIMESTAMP(S.date_finish_desired)-UNIX_TIMESTAMP(S.date_start) AS time_limit,
                IF(S.date_start<=NOW(),UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW()))-UNIX_TIMESTAMP(S.date_start),0) AS time_pass,
                (UNIX_TIMESTAMP(IF(S.state_id=2,S.date_edit,NOW())) - UNIX_TIMESTAMP(T.date_creation)) AS time_start,
                IF(T.bill_no,(
                    SELECT IF(cl.`type`="multi",nai.fio,cl.company) FROM newbills nb
                    LEFT JOIN clients cl ON cl.id = nb.client_id
                    LEFT JOIN newbills_add_info nai ON nai.bill_no = nb.bill_no
                    WHERE nb.bill_no = T.bill_no
                ),
                T.client) AS CLIENT_q,
                is_payed,
                is_rollback,
                tt.name AS trouble_name,
                cl.manager, cl.company
            FROM
                tt_troubles AS T
            LEFT JOIN tt_stages AS S2 ON S2.trouble_id = T.id 
            INNER JOIN tt_stages AS S  ON S.stage_id = T.cur_stage_id AND S.trouble_id = T.id
            LEFT JOIN tt_states AS tts ON tts.id = S.state_id
            LEFT JOIN tt_states_rb AS ttsrb ON ttsrb.id = S.state_id
            LEFT JOIN newbills_add_info nba ON nba.bill_no = T.bill_no
            LEFT JOIN newbills n  ON n.bill_no = T.bill_no
            LEFT JOIN tt_types tt ON tt.code = T.trouble_type
            LEFT JOIN clients cl  ON T.client=cl.client
            WHERE ((T.trouble_type = "shop_orders") AND (T.folder&128))
            GROUP BY T.id
            ',
    'sortable' => [
            'status',
            'id',
            'client',
            'company',
            'created',
            'manager'
    ],
    'order'=> [
        'date_creation' => SORT_DESC, 
    ],
    'countperpage' => 50,
    'columns' => [
            'client' => [
                'label' => 'ИД',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module=clients&id={client}',
             ],
             'company' => [
                'label' => 'Компания',
             ],
            'date_creation' => [
                'label' => 'Создан',
             ],
            'state_name' => [
                'label' => 'Этап',    
             ],
            'bill_no' => [
                'label' => 'Счет',
                'class' => 'app\classes\yii\HrefDataColumn',
                'href'=>'index.php?module={trouble_type}&action=order_view&id={bill_id}',
             ],
            'user_author' => [
                'label' => 'Создал',    
             ],
            'manager' => [
                'label' => 'Менеджер',    
             ],
    ],
];





