<?php

return [

    // Base biller
    'date_once' => ', {0, date, dd}',

    'date_range_month' => ' {0, date, yyyy.MM.dd}-{1, date,yyyy.MM.dd} periódusra',
    'date_range_year' => ' {0, date, yyyy.MM.dd}-{1, date,yyyy.MM.dd} periódusra',
    'date_range_full' => ' {0, date, yyyy.MM.dd}-{1, date,yyyy.MM.dd} periódusra',


    'by_agreement' => ',{contract_date,date,dd MMM yyyy} dátumú {contract_no} számú szerződés szerint',

    // SMS
    'sms_service' => '{tariff} SMS kézbesítés, {date_range}',
    'sms_monthly_fee' => '{tariff}, SMS kézbesítés előfizetési díja',

    // E-mail
    'email_service' => '{local_part}@{domain} postafiók karbantartása, {date_range} {by_agreement}',

    // Extra
    'extra_service' => '{tariff}{date_range}{by_agreement}',
    'extra_service_itpark' => '{contract_date, date, dd MMM yyyy} dátumú {contract} számú szerződés szerint',

    // Welltime
    'welltime_service' => '{tariff}{date_range}',

    // VPBX
    'vpbx_service' => '{tariff} virtuális alközpont előfizetési díja, {date_range}',
    'vpbx_over_disk_usage' => 'Tárhely túllépési díj, {date_range}',
    'vpbx_over_ports_count' => 'Port mennyiség túllépési díj, {date_range}',
    'vpbx_over_ext_did_count' => 'Külső operátori telefonszám bevezetés havidíja, {date_range}',

    //Call_chat
    'call_chat_service' => '{tariff}{date_range}',

    'Replenishment of the account {account} for the amount of {sum} {currency}' => '{account} ügyfélszámú egyenleg feltöltés {sum} {currency} összeggel',
    'HUF' => 'Ft',
    'USD' => '$',

    'incomming_payment' => 'Egyenleg feltöltés távközlési szolgáltatásokra',

    'nal' => 'készpénz',
    'beznal' => 'átutalás',

    'correct_sum' => 'Fogyasztás előző periódusra',
    'Current statement' => 'Aktuális kivonat',
];
