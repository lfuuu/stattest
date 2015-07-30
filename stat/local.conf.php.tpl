<?php

return array(
    'WEB_PATH'            =>    '/',
    'WEB_ADDRESS'        => 'http://stat',
    'USE_MD5'        => 0, //использование md5-хеширование для паролей
    'ADMIN_EMAIL'   => 'admin@mcn.loc',

/* voip regions db
    'R_CALLS_HOST'        =>    '',
    'R_CALLS_USER'        =>    '',
    'R_CALLS_PASS'        =>    '',
    'R_CALLS_DB'        =>    '',
*/

/* voip moscow db
    'R_CALLS_99_HOST'        =>    '',
    'R_CALLS_99_USER'        =>    '',
    'R_CALLS_99_PASS'        =>    '',
    'R_CALLS_99_DB'        =>    '',
*/


/* sync with lk
    'MONGO_HOST' => '',
    'MONGO_USER' => '',
    'MONGO_PASS' => '',
    'MONGO_DB' => '',
*/

/* sync with 1c
    'SYNC1C_UT_SOAP_URL'  => '',
    'SYNC1C_UT_LOGIN'     => '',
    'SYNC1C_UT_PASSWORD'  => '',
    'SYNC1C_STAT_TOKEN'   => '',
*/

/* dir with log files , 
*  stat.mcn.ru => /var/log/nispd/
*/
    "LOG_DIR" => "/tmp/",

    /** ссылка-префикс для вывода публичных счетов */
    'API__print_bill_url' => 'https://lk.mcn.ru/print?bill=', //'https://stat.mcn.ru/tst/bill.php?bill='

    /** параметры для достпа к платежной системе Unileller */
    'UNITELLER_SHOP_ID' => '',
    'UNITELLER_PASSWORD' => '',
/*   dir with scaned docs (for qrcodes response) 
*    stat.mcn.ru: 'SCAN_DOC_DIR'      => '/var/log/skanpdf/';
*/
    'SCAN_DOC_DIR'      => "/tmp/docs/",
    "AUTOCREATE_SIP_ACCOUNT" => 0, //автоматическое создание учеток при заведении номера
    "AUTOCREATE_VPBX" => 0, // автоматическое создание vpbx после включения услуги

  'paypal_user' => '',
  'paypal_password' => '',
  'paypal_signature' => ''
);
