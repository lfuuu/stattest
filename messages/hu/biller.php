<?php

return [

    // Base biller
    'date_once' => ', {0, date, YYYY.MM.dd}.',
    'date_range_full' => ' {0, date, YYYY.MM.dd} -{1, date, YYYY.MM.dd}',
    'date_range_with_year' => ' {0, date, YYYY.MM.dd} -{1, date, YYYY.MM.dd}',
    'date_ext_range_full' => ' с{0, date, dd} по{1, date, dd MMMM YYYY}',
    'date_ext_range_with_year' => ' с{0, date, dd MMMM} по{1, date, dd MMMM YYYY}',

    // VPBX
    'vpbx_service' => '{tariff} virtuális alközpont előfizetési díja{date_range}',
    'vpbx_over_disk_usage' => 'Tárhely túllépés{date_range}',
    'vpbx_over_ports_count' => 'Portok számának a túllépése{date_range}',

    '' => '{}'

];