<?php

$default = [
    'from' => [
        'grid_settings gs',
        'clients c',
        'client_contract cr',
        'client_contragent cg'
    ],
    'select' => [
        'c.status',
        'c.id',
        'cg.name AS company',
        'cr.manager',
        'c.support',
        'c.telemarketing',
        'sch.name AS sale_channel',
        'c.created',
        'c.currency',
    ],
    'where' => [
        //sintaxis like AR->andWhere()
        ['c.contract_id = cr.id'],
        ['cr.contragent_id = cg.id'],
        ['cs.grid_business_process_id = cr.business_process_id'],
    ],
    'leftJoin' => [
        'sale_channels sch ON sch.id = c.sale_channel'
    ]
];

return [
    19 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 19],
        ]
    ]),
    8 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 8],
        ]
    ]),
    9 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 9],
        ]
    ]),
    10 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 10],
        ]
    ]),
    11 => array_merge_recursive($default, [
        'where' => [
            ['c.is_blocked' => 1],
            ['cr.business_process_status_id' => 9],
        ]
    ]),
    21 => array_merge_recursive($default, [
        'where' => [
            ['c.is_blocked' => 1],
            ['!=', 'cr.business_process_status_id' => 8],
            ['!=', 'cr.business_process_status_id' => 11],
        ]
    ]),
    22 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 22],
        ]
    ]),
    23 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 0],
        ]
    ]),
    27 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 27],
        ]
    ]),
    28 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 28],
        ]
    ]),
    29 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 2],
            ['cr.business_process_status_id' => 29],
        ]
    ]),
    1 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => 'income'],
            ['cr.contract_type_id' => 2],
        ]
    ]),
    2 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => 'negotiations'],
            ['cr.contract_type_id' => 2],
        ]
    ]),
    3 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => 'testing'],
            ['cr.contract_type_id' => 2],
        ]
    ]),
    4 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => 'connecting'],
            ['cr.contract_type_id' => 2],
        ]
    ]),
    5 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => 'tech_deny'],
            ['cr.contract_type_id' => 2],
        ]
    ]),
    6 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => 'deny'],
            ['cr.contract_type_id' => 2],
        ]
    ]),
    7 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => ['double', 'trash']],
            ['cr.contract_type_id' => 2],
        ]
    ]),
    16 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => ['double', 'trash']],
            ['cr.contract_type_id' => 5],
        ]
    ]),
    18 => array_merge_recursive($default, [
        'where' => [
            ['c.status' => ['double', 'trash']],
            ['cr.contract_type_id' => 5],
        ]
    ]),
    32 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 32],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    36 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 36],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    108 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 108],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    109 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 109],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    110 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 110],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    15 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 15],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    92 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 92],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    93 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 93],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    94 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 94],
            ['cr.contract_type_id' => 4],
        ]
    ]),
    24 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 24],
            ['cr.contract_type_id' => 7],
        ]
    ]),
    35 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 35],
            ['cr.contract_type_id' => 7],
        ]
    ]),
    26 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 26],
            ['cr.contract_type_id' => 7],
        ]
    ]),
    30 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 1],
        ]
    ]),
    34 => array_merge_recursive($default, [
        'where' => [
            ['cr.contract_type_id' => 6],
        ]
    ]),
    37 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 37],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    38 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 38],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    39 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 39],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    40 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 40],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    107 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 107],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    41 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 41],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    42 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 42],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    43 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 43],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    44 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 44],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    45 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 45],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    47 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 47],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    48 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 48],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    49 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 49],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    50 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 50],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    51 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 51],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    52 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 52],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    53 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 53],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    54 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 54],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    55 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    62 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 62],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    63 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 63],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    64 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 64],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    65 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 65],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    66 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 66],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    67 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 67],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    68 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 68],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    69 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 69],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    70 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    77 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 77],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    78 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 78],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    79 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 79],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    80 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 80],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    81 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 81],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    82 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 82],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    83 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 83],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    84 => array_merge_recursive($default, [
        'where' => [
            ['cr.business_process_status_id' => 84],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    85 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 3],
        ]
    ]),
    95 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 95],
        ]
    ]),
    96 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 96],
        ]
    ]),
    97 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 97],
        ]
    ]),
    98 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 98],
        ]
    ]),
    99 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 99],
        ]
    ]),
    100 => array_merge_recursive($default, [
        'where' => [
            ['!=','cr.business_process_status_id', 11],
            ['cr.contract_type_id' => 100],
        ]
    ]),
    101 => array_merge_recursive($default, [
        'select' => [
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
            'sum(l.sum) as total'
        ],
        'from' => [
            'newbills b',
            'newbill_lines l',
        ],
        'where' => [
            'c.id=b.client_id',
            'l.bill_no=b.bill_no',
            ['b.is_payed' => 1],
            ['l.type' => 'service'],
            ['<>', 'l.service', ['1C','bill_monthlyadd','','all4net']],
        ],
        'group' => [
            'l.service',
            'c.id'
        ]
    ]),
    102 => array_merge_recursive($default, [
        'select' => [
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
            'sum(l.sum) as total'
        ],
        'from' => [
            'newbills b',
            'newbill_lines l',
        ],
        'where' => [
            'c.id=b.client_id',
            'l.bill_no=b.bill_no',
            ['b.is_payed' => 1],
            ['l.type' => 'service'],
            ['<>', 'l.service', ['1C','bill_monthlyadd','','all4net']],
        ],
        'group' => [
            'c.account_manager'
        ]
    ]),
    103 => array_merge_recursive($default, [
        'select' => [
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
            'sum(l.sum) as total'
        ],
        'from' => [
            'newbills b',
            'newbill_lines l',
        ],
        'where' => [
            'c.id=b.client_id',
            'l.bill_no=b.bill_no',
            ['b.is_payed' => 1],
            ['l.type' => 'service'],
            ['<>', 'l.service', ['1C','bill_monthlyadd','','all4net']],
        ],
        'group' => [
            'c.account_manager'
        ]
    ]),
    104 => array_merge_recursive($default, [
        'select' => [
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
            'sum(l.sum) as total'
        ],
        'from' => [
            'newbills b',
            'newbill_lines l',
        ],
        'where' => [
            'c.id=b.client_id',
            'l.bill_no=b.bill_no',
            ['b.is_payed' => 1],
            ['l.type' => 'service'],
            ['<>', 'l.service', ['1C','bill_monthlyadd','','all4net']],
        ],
        'group' => [
            'l.service',
        ]
    ]),
    106 => array_merge_recursive($default, [
        'select' => [
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=0,l.sum,0)) AS abon',
            'SUM(IF(MONTH(l.date_from)-MONTH(b.bill_date)=-1 OR MONTH(l.date_from)-MONTH(b.bill_date)=11,l.sum,0)) AS over',
            'sum(l.sum) as total'
        ],
        'from' => [
            'newbills b',
            'newbill_lines l',
        ],
        'where' => [
            'c.id=b.client_id',
            'l.bill_no=b.bill_no',
            //['b.is_payed' => 1],
            ['l.type' => 'service'],
            ['<>', 'l.service', ['1C','bill_monthlyadd','','all4net']],
        ],
        'group' => [
            'l.service',
            'c.id'
        ]
    ]),
];