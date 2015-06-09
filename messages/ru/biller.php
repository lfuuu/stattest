<?php

return [

    // Base biller
    'serial_number' => '(серийный номер {value}',
    'pledge' => 'Залог за {value}',
    'date_once' => ', {0, date, dd}',
    'date_range' => ' с{0, date, dd} по{1, date, dd MMM}',
    'date_range_w_year' => ' с{0, date, dd MMM YYYY} по{1, date, dd MMM YYYY}',
    'by_agreement' => ', согласно Договора {contract_no} от {contract_date, date,dd MMM YYYY}',

    // SMS
    'sms_service' => 'СМС рассылка, {tariff}{date_range}',
    'sms_monthly_fee' => 'Абонентская плата за СМС рассылки, {tariff}',

    // E-mail
    'email_service' => 'Поддержка почтового ящика {local_part}@{domain}{date_range}{by_agreement}',

    // Extra
    'extra_service' => '{tariff}{by_agreement}',
    'extra_service_itpark' => 'по Договору {contract} от {contract_date, date, dd MMM YYYY} г.',

    // Welltime
    'welltime_service' => '{tariff}{date_range}',

    // VPBX
    'vpbx_service' => '{tariff}{date_range}',
    'vpbx_over_disk_usage' => 'Превышение дискового пространства{date_range}',
    'vpbx_over_ports_count' => 'Превышение количества портов{date_range}',

];