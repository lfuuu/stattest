<?php

return [

    // Base biller
    'date_once' => ', {0, date, YYYY.MM.dd}.',
    'date_range' => ' {0, date, YYYY.MM.dd}. -{1, date, YYYY.MM.dd}.',
    'date_range_w_year' => ' {0, date, YYYY.MM.dd}. -{1, date, YYYY.MM.dd}.',

    // VPBX
    'vpbx_service' => 'Virtuális alközpont előfizetési díja {tariff}{date_range}',
    'vpbx_over_disk_usage' => 'Tárhely túllépés{date_range}',
    'vpbx_over_ports_count' => 'Portok számának a túllépése{date_range}',

];