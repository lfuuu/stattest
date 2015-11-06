<?php

return [

    // Base biller
    'serial_number' => '(серийный номер {value}',
    'pledge' => 'Залог за {value}',
    'date_once' => ', {0, date, dd}',

    'date_range_month' => ' с{0, date, dd} по {1, date, dd MMMM}',
    'date_range_year' =>  ' с{0, date, dd MMMM} г. по {1, date, dd MMMM YYYY} г.',
    'date_range_full' =>  ' с{0, date, dd MMMM YYYY} г. по {1, date, dd MMMM YYYY} г.',


    'by_agreement' => ', согласно Договора {contract_no} от {contract_date, date,dd MMM YYYY} г.',

    // SMS
    'sms_service' => 'СМС рассылка, {tariff}{date_range}',
    'sms_monthly_fee' => 'Абонентская плата за СМС рассылки, {tariff}',

    // E-mail
    'email_service' => 'Поддержка почтового ящика {local_part}@{domain}{date_range}{by_agreement}',

    // Extra
    'extra_service' => '{tariff}{date_range}{by_agreement}',
    'extra_service_itpark' => 'по Договору {contract} от {contract_date, date, dd MMM YYYY} г.',

    // Welltime
    'welltime_service' => '{tariff}{date_range}',

    // VPBX
    'vpbx_service' => '{tariff}{date_range}',
    'vpbx_over_disk_usage' => 'Превышение дискового пространства{date_range}',
    'vpbx_over_ports_count' => 'Превышение количества портов{date_range}',

    'test' => '',

];
