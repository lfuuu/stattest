<?php

return [

    // Base biller
    'date_once' => ', {0, date, dd}',

    'date_range_month' => ' from {0, date, dd} to {1, date, dd MMMM}',
    'date_range_year' => ' from {0, date, dd MMMM} to {1, date, dd MMMM yyyy}',
    'date_range_full' => ' from {0, date, dd MMMM yyyy} to {1, date, dd MMMM yyyy}',


    'by_agreement' => ', according to the contract {contract_no} from {contract_date,date,dd MMMM yyyy} г.',

    // SMS
    'sms_service' => 'SMS sending, {tariff}{date_range}',
    'sms_monthly_fee' => 'Subscription fee for SMS delivery, {tariff}',

    // E-mail
    'email_service' => 'Mailbox support {local_part}@{domain}{date_range}{by_agreement}',

    // Extra
    'extra_service' => '{tariff}{date_range}{by_agreement}',
    'extra_service_itpark' => 'under the Contract {contract} от {contract_date, date, dd MMMM yyyy} г.',

    // Welltime
    'welltime_service' => '{tariff}{date_range}',

    // VPBX
    'vpbx_service' => 'VATS {tariff}{date_range}',
    'vpbx_over_disk_usage' => 'Exceeding the disk space {date_range}',
    'vpbx_over_ports_count' => 'Number of ports exceeded {date_range}',
    'vpbx_over_ext_did_count' => 'Subscriber fee for the service of connecting the number of the third-party operator {date_range}',

    //Call_chat
    'call_chat_service' => '{tariff}{date_range}',

    'test' => '',

    'USD' => '$',

    'incomming_payment' => 'Advance payment for communication services',

    'Communications services contract #{contract_number}' => 'Communication services under contract No.{contract_number}',

    'nal' => 'cash',
    'beznal' => 'transfer',

    'correct_sum' => 'Correction sum',
    'Current statement' => 'Current statement',
];
