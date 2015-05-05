<?php

$config = array(
    'WEB_ADDRESS'        => '',
    'WEB_PATH'            =>    '',
    'USE_MD5'           => 1,

// stat mysql
    'SQL_ATS_DB'        =>    '',

// voip regions db
    'R_CALLS_HOST'        =>    '',
    'R_CALLS_USER'        =>    '',
    'R_CALLS_PASS'        =>    '',
    'R_CALLS_DB'        =>    '',

// welltime db
    'EXT_SQL_HOST'        =>  '',
    'EXT_SQL_USER'        =>  '',
    'EXT_SQL_PASS'        =>  '',
    'EXT_SQL_DB'        =>  '',
    'EXT_GROUP_ID'        =>  6,

// sync with lk
    'MONGO_HOST' => '',
    'MONGO_USER' => '',
    'MONGO_PASS' => '',
    'MONGO_DB' => '',

// sync with 1c
    'SYNC1C_UT_SOAP_URL'  => '',
    'SYNC1C_UT_LOGIN'     => '',
    'SYNC1C_UT_PASSWORD'  => '',
    'SYNC1C_STAT_TOKEN'   => '',


    'SERVER'            =>    'tiberis',
    'PLATFORM'            =>    'unix',
    'DEBUG_LEVEL'        =>    1,
    'SMTP_SERVER'        =>     'smtp.mcn.ru',
    'MAIL_TEST_ONLY'    =>    0,
    
    'PATH_TO_ROOT'      => dirname(__FILE__)."/",
    "LOG_DIR"           => "/tmp/"

);

$config = array_merge($config, require(dirname(__FILE__).'/local.conf.php'));

if (preg_match_all('/host=([\w\.]+);dbname=(\w+)/i', Yii::$app->db->dsn, $matches)) {
    $config['SQL_HOST'] = $matches[1][0];
    $config['SQL_DB'] = $matches[2][0];
    $config['SQL_USER'] = Yii::$app->db->username;
    $config['SQL_PASS'] = Yii::$app->db->password;
} else {
    $config['SQL_HOST'] = '';
    $config['SQL_DB'] = '';
    $config['SQL_USER'] = '';
    $config['SQL_PASS'] = '';
}

if (preg_match_all('/host=([\w\.]+);.*dbname=(\w+)/i', Yii::$app->dbPg->dsn, $matches)) {
    $config['PGSQL_HOST'] = $matches[1][0];
    $config['PGSQL_DB'] = $matches[2][0];
    $config['PGSQL_USER'] = Yii::$app->dbPg->username;
    $config['PGSQL_PASS'] = Yii::$app->dbPg->password;
} ELSE {
    $config['PGSQL_HOST'] = '';
    $config['PGSQL_DB'] = '';
    $config['PGSQL_USER'] = '';
    $config['PGSQL_PASS'] = '';
}


if(!defined("NO_WEB")) {
    header("Content-type:text/html; charset=utf-8");
    header("X-XSS-Protection: 0");
}

if(isset($_GET["savesql"])) {
    define("save_sql", 1);
}

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") {
    define('PROTOCOL_STRING','https://');
} else {
    define('PROTOCOL_STRING','http://');
}


foreach ($config as $config_key=>$config_value) {
    if (!defined($config_key)) 
        define($config_key,$config_value);
}
unset($config_key); unset($config_value);

setlocale(LC_CTYPE,'ru_RU.utf-8');

ini_set('SMTP',SMTP_SERVER);

define('PAGE_OBJ_COUNT',	50);

define('INCLUDE_PATH',          realpath(PATH_TO_ROOT) . '/include/');
define('MODELS_PATH',           realpath(PATH_TO_ROOT) . '/models/');
define('CLASSES_PATH',          realpath(PATH_TO_ROOT) . '/classes/');
define('MODULES_PATH',          realpath(PATH_TO_ROOT) . '/modules/');
define('INCLUDE_ARCHAIC_PATH',  realpath(PATH_TO_ROOT) . '/include_archaic/');
define('DESIGN_PATH',           realpath(PATH_TO_ROOT) . '/design/');
define('DESIGNC_PATH',          realpath(PATH_TO_ROOT) . '/design_c/');

define('PAYMENTS_FILES_PATH', realpath(PATH_TO_ROOT . '../../') . '/store/payments/');
define('STORE_PATH',            realpath(PATH_TO_ROOT . '../../') . '/store/');
define('SOUND_PATH',            realpath(PATH_TO_ROOT) . '/sound/');

define('WEB_IMAGES_PATH',       WEB_PATH . 'images/');
define('WEB_SOUND_PATH',        WEB_PATH . 'sound/');

//define('SUM_ADVANCE',199);
define('SUM_ADVANCE',100);
define('SUM_PHONE_ADVANCE',79.67);

if (DEBUG_LEVEL!=0) ini_set ("display_errors", "On");

require_once(CLASSES_PATH . 'Autoload.php');

global $db, $pg_db, $db_ats;

if (!defined('NO_INCLUDE')){
    require_once(INCLUDE_PATH.'util.php');
    require_once(INCLUDE_PATH.'clCards.php');

    require_once(INCLUDE_PATH.'sql.php');
    $db        = new MySQLDatabase();

    if (defined("SQL_ATS2_DB") && SQL_ATS2_DB) {
        $db_ats = new MySQLDatabase(SQL_HOST, SQL_USER, SQL_PASS, SQL_ATS2_DB);
    } else {
        $db_ats = &$db;
    }

    require_once(INCLUDE_PATH.'pgsql.php');
    $pg_db    = new PgSQLDatabase();

    require_once INCLUDE_PATH.'db_form.php';
    require_once(INCLUDE_PATH.'modules.php');

    if (!defined('NO_WEB')){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once(INCLUDE_PATH.'mysmarty.php');

        require_once(INCLUDE_PATH.'authuser.php');
    }
    require_once(INCLUDE_PATH.'writeoff.php');
}

ActiveRecord\Config::initialize(function($cfg) {
    $connections = array(
        'db' => 'mysql://' . SQL_USER . ':' . SQL_PASS . '@' . SQL_HOST . '/' . SQL_DB . '?charset=utf8',
        'voip' => 'pgsql://' . PGSQL_USER . ':' . PGSQL_PASS . '@' . PGSQL_HOST . '/' . PGSQL_DB . '?charset=utf8',
    );

    $cfg->set_model_directory(MODELS_PATH);
    $cfg->set_connections($connections, 'db');
});

function trigger_error2($string) {
    Yii::$app->session->addFlash('error', $string);
}
