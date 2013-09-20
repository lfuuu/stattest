<?php

return array(
    'WEB_PATH'            =>    '/',
    'WEB_ADDRESS'        => 'http://stat',
    'USE_MD5'        => 0, //использование md5-хеширование для паролей

// stat mysql
    'SQL_HOST'            =>    '',
    'SQL_USER'            =>    '',
    'SQL_PASS'            =>    '',
    'SQL_DB'            =>    '',
    'SQL_ATS_DB'        =>    '',


/* voip central db
    'PGSQL_HOST'        =>    '',
    'PGSQL_USER'        =>    '',
    'PGSQL_PASS'        =>    '',
    'PGSQL_DB'            =>    '',
*/

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


/* welltime db
    'EXT_SQL_HOST'        =>  '',
    'EXT_SQL_USER'        =>  '',
    'EXT_SQL_PASS'        =>  '',
    'EXT_SQL_DB'        =>  '',
    'EXT_GROUP_ID'        =>  6,
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
 (stat.mcn.ru => /var/log/nispd/)
 */
    "LOG_DIR" => "/tmp/",
);
