<?php

if(!defined("NO_WEB"))
{
	header("Content-type:text/html; charset=koi8-r");
	header("X-XSS-Protection: 0");
}

if(isset($_GET["savesql"]))
    define("save_sql", 1);

	define('SERVER_DEFAULT',	'stat.mcn.ru');	//необходим для тех скриптов, которые не могут прочитать переменные Apache. например, autoping.php
	$SERVERS=array();

	$SERVERS['teststat.mcn.ru']=array(
		'SERVER'			=>	'teststat',
		'SQL_HOST'			=>	'localhost',
		'SQL_USER'			=>	'root',
		'SQL_PASS'			=>	'',
		'SQL_DB'			=>	'nispd_test',
		'SQL_ATS_DB'		=>	'ats_test',
		'PGSQL_HOST'		=>	'eridanus.mcn.ru',
		'PGSQL_USER'		=>	'eivanov',
		'PGSQL_PASS'		=>	'terem0k@@',
		'PGSQL_DB'			=>	'nispd',
		'R_CALLS_HOST'		=>	'reg[region].mcntelecom.ru',
		'R_CALLS_USER'		=>	'stat',
		'R_CALLS_PASS'		=>	'BLS21hnoRDtA3Id4ueWSg5IPMC5B19fl',
		'R_CALLS_DB'		=>	'nispd[region]',
		'EXT_SQL_HOST'		=>  '',
		'EXT_SQL_USER'		=>  '',
		'EXT_SQL_PASS'		=>  '',
		'EXT_SQL_DB'		=>  '',
		'EXT_GROUP_ID'		=>  6,
		'PLATFORM'			=>	'unix',
		'DEBUG_LEVEL'		=>	1,
		'WEB_PATH'			=>	'/',
		'DB_SETUP_COLLATES'	=>	1,
		'SMTP_SERVER'		=> 	'smtp.mcn.ru',
		'MAIL_TEST_ONLY'	=>	0,
		'WEB_ADDRESS'		=> 'http://teststat.mcn.ru',
	);

	$SERVERS['89.235.136.22']=array(
		'SERVER'			=>	'127.0.0.1',
		'SQL_HOST'			=>	'127.0.0.1',
		'SQL_USER'			=>	'root',
		'SQL_PASS'			=>	'',
		'SQL_DB'			=>	'nispd',
		'SQL_ATS_DB'		=>	'ats',
		'PGSQL_HOST'		=>	'eridanus.mcn.ru',
		'PGSQL_USER'		=>	'eivanov',
		'PGSQL_PASS'		=>	'terem0k@@',
		'PGSQL_DB'			=>	'nispd',
		'R_CALLS_HOST'		=>	'reg[region].mcntelecom.ru',
		'R_CALLS_USER'		=>	'stat',
		'R_CALLS_PASS'		=>	'BLS21hnoRDtA3Id4ueWSg5IPMC5B19fl',
		'R_CALLS_DB'		=>	'nispd[region]_dev',
		'EXT_SQL_HOST'		=>  '',
		'EXT_SQL_USER'		=>  '',
		'EXT_SQL_PASS'		=>  '',
		'EXT_SQL_DB'		=>  '',
		'EXT_GROUP_ID'		=>  6,
		'PLATFORM'			=>	'unix',
		'DEBUG_LEVEL'		=>	1,
		'WEB_PATH'			=>	'/',
		'DB_SETUP_COLLATES'	=>	1,
		'SMTP_SERVER'		=> 	'smtp.mcn.ru',
		'MAIL_TEST_ONLY'	=>	1,
		'WEB_ADDRESS'		=> 'http://89.235.136.22',

		'DEBUG_TABLE'		=> 'DEBUG',

		'SYNC1C_UT_SOAP_URL'  => 'http://stattest.ws.dionis.mcn.ru/ws/ws/stat',
		'SYNC1C_UT_LOGIN'     => 'web_service',
		'SYNC1C_UT_PASSWORD'  => 'sdfg94w758ht23g4r78394g',
		'SYNC1C_STAT_TOKEN'   => '',
	);

	$SERVERS['stat.mcn.ru']=array(
		'SERVER'			=>	'tiberis',
		'SQL_HOST'			=>	'localhost',
		'SQL_USER'			=>	'stat_operator',
		'SQL_PASS'			=>	'3616758a',
		'SQL_DB'			=>	'nispd',
		'SQL_ATS_DB'		=>	'ats',
		'PGSQL_HOST'		=>	'eridanus.mcn.ru',
		'PGSQL_USER'		=>	'eivanov',
		'PGSQL_PASS'		=>	'terem0k@@',
		'PGSQL_DB'			=>	'nispd',
		'R_CALLS_HOST'		=>	'reg[region].mcntelecom.ru',
		'R_CALLS_USER'		=>	'stat',
		'R_CALLS_PASS'		=>	'BLS21hnoRDtA3Id4ueWSg5IPMC5B19fl',
		'R_CALLS_DB'		=>	'nispd[region]',
		'EXT_SQL_HOST'		=>  'thiamis.mcn.ru',
		'EXT_SQL_USER'		=>  'stat',
		'EXT_SQL_PASS'		=>  'passwtmcnru',
		'EXT_SQL_DB'		=>  'welltone_new3',
		'EXT_GROUP_ID'		=>  6,
		'PLATFORM'			=>	'unix',
		'DEBUG_LEVEL'		=>	1,
		'WEB_PATH'			=>	'/operator/',
		'DB_SETUP_COLLATES'	=>	1,
		'SMTP_SERVER'		=> 	'smtp.mcn.ru',
		'MAIL_TEST_ONLY'	=>	0,
		'WEB_ADDRESS'		=> 'https://stat.mcn.ru',

		'MONGO_HOST' => 'lk.mcn.ru',
		'MONGO_USER' => 'lkmcn',
		'MONGO_PASS' => 'Ummhsn3iqCWA',
		'MONGO_DB' => 'lkmcn',

		'SYNC1C_UT_SOAP_URL'  => 'http://stat.ws.dionis.mcn.ru/ws/ws/stat',
		'SYNC1C_UT_LOGIN'     => 'web_service',
		'SYNC1C_UT_PASSWORD'  => 'sdfg94w758ht23g4r78394g',
		'SYNC1C_STAT_TOKEN'   => '43fb37aba737439f2ae2fa5da242d310ed3939d087c7926765e9e13e593b5772a706248935d573312fa3061cf6a71b4477c2e851c28284e8df346bc19de19ddf',
	);

$sPath = strtolower($_SERVER["SCRIPT_FILENAME"]);
if(strpos($sPath, "tst") !== false || (isset($_SERVER["PWD"]) &&  strpos($_SERVER["PWD"], "test") !== false) )
{
    if(isset($_GET["db"]) && $_GET["db"] == "real")
        $a=1;
    else{
        $SERVERS['stat.mcn.ru']['SQL_DB'] =	'test_operator';
        $SERVERS['stat.mcn.ru']['SQL_ATS_DB'] =	'test_ats';
    }

	$SERVERS['stat.mcn.ru']['SQL_USER'] =	'latyntsev';
	$SERVERS['stat.mcn.ru']['SQL_PASS'] =	'kxpyLNJ';
	$SERVERS['stat.mcn.ru']['PGSQL_HOST']	=	'eridanus.mcn.ru';
	$SERVERS['stat.mcn.ru']['PGSQL_USER']	=	'eivanov';
	$SERVERS['stat.mcn.ru']['PGSQL_PASS']	=	'terem0k@@';
	$SERVERS['stat.mcn.ru']['PGSQL_DB']		=	'nispd';
	$SERVERS['stat.mcn.ru']['EXT_SQL_HOST'] =  '';
	$SERVERS['stat.mcn.ru']['EXT_SQL_USER'] =  '';
	$SERVERS['stat.mcn.ru']['EXT_SQL_PASS'] =  '';
	$SERVERS['stat.mcn.ru']['EXT_SQL_DB'] =  '';
	$SERVERS['stat.mcn.ru']['WEB_PATH'] =	'/tst/';

	define('PAYMENTS_FILES_PATH',	'../../include/store/payments/');
}
//
	$s_s=isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:
			(isset($_SERVER['COMPUTERNAME'])?$_SERVER['COMPUTERNAME']:SERVER_DEFAULT);
	if (!isset($SERVERS[$s_s])) die("Please, configure. ".$s_s);
	foreach ($SERVERS[$s_s] as $s_n=>$s_v) if (!defined($s_n)) define($s_n,$s_v);
	unset($s_s); unset($s_n); unset($s_v);

	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") {
		define('PROTOCOL_STRING','https://');
	} else {
		define('PROTOCOL_STRING','http://');
	}
	ini_set('SMTP',SMTP_SERVER);
	//ini_set('memory_limit','24M');
	ini_set('upload_max_filesize','32M');

	if (PLATFORM=="windows") {
		setlocale(LC_CTYPE,'Russian_Russia.866');
	} else {
		setlocale(LC_CTYPE,'ru_RU.koi8-r');
	}

    date_default_timezone_set("Asia/Dubai"); 
		
    date_default_timezone_set("Asia/Dubai");

  define('PAGE_OBJ_COUNT',	50);
  define('USE_MD5',			0);
  define('INCLUDE_PATH',    PATH_TO_ROOT.'include/');
  define('MODELS_PATH',			PATH_TO_ROOT.'models/');
  define('CLASSES_PATH',		PATH_TO_ROOT.'classes/');
  define('MODULES_PATH',		PATH_TO_ROOT.'modules/');
  define('INCLUDE_ARCHAIC_PATH',	PATH_TO_ROOT.'include_archaic/');
  define('DESIGN_PATH',			PATH_TO_ROOT.'design/');
  define('DESIGNC_PATH',		PATH_TO_ROOT.'design_c/');
  define('LETTER_FILES_PATH',	PATH_TO_ROOT.'store/letters/');

    if(!defined("PAYMENTS_FILES_PATH"))
        define('PAYMENTS_FILES_PATH',	PATH_TO_ROOT.'store/payments/');
	define('STORE_PATH',			PATH_TO_ROOT.'store/');
	define('SOUND_PATH',			PATH_TO_ROOT.'sound/');

	define('WEB_IMAGES_PATH',		WEB_PATH.'images/');
	define('WEB_SOUND_PATH',		WEB_PATH.'sound/');

	//define('SUM_ADVANCE',199);
	define('SUM_ADVANCE',100);
	define('SUM_PHONE_ADVANCE',79.67);
		
	if (DEBUG_LEVEL!=0) ini_set ("display_errors", "On");

    require_once(CLASSES_PATH . 'Autoload.php');

	if (!defined('NO_INCLUDE')){
		if (defined('NO_WEB') || defined('ERROR_NO_WEB')){
			require_once(INCLUDE_PATH.'error_noweb.php');
		} else {
			require_once(INCLUDE_PATH.'error.php');
		}
		require_once(INCLUDE_PATH.'util.php');
		require_once(INCLUDE_PATH.'util_session.php');
		require_once(INCLUDE_PATH.'clCards.php');

		require_once(INCLUDE_PATH.'sql.php');
		$db		= new MySQLDatabase();
    require_once(INCLUDE_PATH.'pgsql.php');
		$pg_db		= new PgSQLDatabase();

		require_once INCLUDE_PATH.'db_form.php';
		require_once(INCLUDE_PATH.'modules.php');

		if (!defined('NO_WEB')){
			require_once(INCLUDE_PATH.'mysmarty.php');
			$design = new MySmarty();

			require_once(INCLUDE_PATH.'user.php');
			$user	= new User();

			$modules= new Modules();//array('users','clients','tt','routers','monitoring'));
		}
		require_once(INCLUDE_PATH.'writeoff.php');
	}

    ActiveRecord\Config::initialize(function($cfg) {
        $connections = array(
            'db' => 'mysql://' . SQL_USER . ':' . SQL_PASS . '@' . SQL_HOST . '/' . SQL_DB . ';charset=koi8r',
        );

        $cfg->set_model_directory(MODELS_PATH);
        $cfg->set_connections($connections, 'db');
    });
?>
