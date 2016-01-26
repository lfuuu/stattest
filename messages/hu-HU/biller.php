<?php

return [

    // Base biller
    'date_once' => ', {0, date, YYYY.MM.dd}.',
    'date_range_month' => ' {0, date, YYYY.MM.dd} -{1, date, YYYY.MM.dd}',
    'date_range_year' =>  ' {0, date, YYYY.MM.dd} -{1, date, YYYY.MM.dd}',
    'date_range_full' =>  ' {0, date, YYYY.MM.dd} -{1, date, YYYY.MM.dd}',

    // VPBX
    'vpbx_service' => '{tariff} virtuális alközpont előfizetési díja{date_range}',
    'vpbx_over_disk_usage' => 'Tárhely túllépés{date_range}',
    'vpbx_over_ports_count' => 'Portok számának a túllépése{date_range}',

    '' => '{}',

    'paypal_payment_description' => '{account} számú egyenleg feltőltése {sum} {currency}',
    'RUB' => 'rub',
    'HUF' => 'Ft',
    'USD' => '$',

    'incomming_payment' => 'Fizetés',
];
