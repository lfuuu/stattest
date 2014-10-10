<?
	define("PATH_TO_ROOT",'../../stat/');
	include PATH_TO_ROOT."conf.php";
	
	$module='register';
	$action=get_param_raw('action','default');
	$design->AddMain('errors.tpl');
	include MODULES_PATH.'register/fakemodule.php';
	//$modules->GetMain($module,$action,null);
	$reg = new m_register();
	$reg->GetMain($action);
	header('Content-Type: text/html; charset=utf-8');
	$design->Process();
?>
