<?
//error_reporting (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_ERROR | E_WARNING | E_ALL );
error_reporting (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_ERROR | E_WARNING);
/*
function error_init(){
	global $G;
	if (!isset($G['error_handling'])){
		set_error_handler("error_function");
		$G['error_handling']=1;
	}
}
function error_close(){
	global $G;
	restore_error_handler();	
	unset($G['error_handling']);
}

function error_function($errno, $errstr, $errfile, $errline){
	if (error_reporting()==0) return;
	echo '['.$errfile.':'.$errline.'] '.$errstr."\n";
};

if (!defined('ERROR_NO')) error_init();*/
?>