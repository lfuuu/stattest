<?php
	define ('NO_INCLUDE',1);
	if (file_exists('../../conf.php')) {
		define('PATH_TO_ROOT','../../');
		require_once('../../conf.php');
	} else if (file_exists('../conf.php')) {
		define('PATH_TO_ROOT','../');
		require_once('../conf.php');
	} else die("Can't find config file");
    $GLOBALS['db_host']=SQL_HOST;
    $GLOBALS['db_user']=SQL_USER;
    $GLOBALS['db_pswd']=SQL_PASS;
    $GLOBALS['db_name']=SQL_DB;
?>