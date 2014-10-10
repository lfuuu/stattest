<?
	//этот файл может использоваться для аяксовых вызовов. и всё.
	define("PATH_TO_ROOT",'../../stat/');
	include PATH_TO_ROOT."conf.php";
	require_once(INCLUDE_PATH.'authuser.php');

	$module=get_param_raw('module','');
	$action=get_param_raw('action','');
	$user->DoAction("");
	$user->DenyInauthorized();
	if(isset($_SESSION['clients_client']))
		$fixclient = $_SESSION['clients_client'];
	else
		$fixclient = null;
	$v="module_".$module; $c = '';
	$f = MODULES_PATH.$module."/header.php";
	if (file_exists($f) && include_once($f)) {
		$c = 'm_'.$module.'_head';
	} else {
		$f = MODULES_PATH.$module."/module.php";
		if (file_exists($f) && include_once($f)) $c = 'm_'.$module;
	}
	if (!$c) exit;
	$$v = new $c();
	header('Content-Type: text/html; charset=utf-8');
	$$v->GetMain($action,$fixclient);
?>
