<?
//error_reporting (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_ERROR | E_WARNING | E_ALL );
//error_reporting (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_ERROR | E_WARNING | E_NOTICE | E_PARSE);
#error_reporting (E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_WARNING);
error_reporting (E_ALL);

function error_init(){
	global $G;
	if (!isset($G['error_handling'])){
		set_error_handler("error_function");
		$G['error_handling']=1;
	}
	if (!isset($G['errors'])) $G['errors']=array();
	if (!isset($G['notices'])) $G['notices']=array();
}

function error_close(){
	global $G;
	restore_error_handler();	
	unset($G['error_handling']);
}

function error_function($errno, $errstr, $errfile, $errline){
	global $G;
	if (error_reporting()==0) return;
	switch ($errno) {
	case E_USER_ERROR:
		$G['notices'][]=array($errstr,$errfile,$errline,$errno);
		break;
	case E_ERROR:
		$G['errors'][]=array($errstr,$errfile,$errline,$errno);
		break;
	case E_USER_WARNING:
		$G['notices'][]=array($errstr,$errfile,$errline,$errno);
		break;
	case E_WARNING:
	case E_NOTICE:
		$G['errors'][]=array($errstr,$errfile,$errline,$errno);
		break;
	case E_USER_NOTICE:
		$G['notices'][]=array($errstr,$errfile,$errline,$errno);
		break;
	default:
//		echo "Unkown error type: [$errno] $errstr $errline $errfile<br>\n";
		break;
	};

    if(false && ((isset($G["error"]) && $G["error"])|| (isset($G["notices"]) && $G["notices"])))
    {
        $pFile = fopen("./error.log", "a+");
        fwrite($pFile, "\n=================================\n".
                date("r").": ".print_r($G, true)."\n".print_r($_GET, true)."\n".print_r($_POST, true)."\n".print_r($_SESSION, true));
        fclose($pFile);
    }
};

error_init();
?>
