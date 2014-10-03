<?
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";

	include PATH_TO_ROOT."include/MyDBG.php";

//аутентификация
	$action=get_param_raw('action','default');
    $user->DoAction($action); if ($action=='login') {
        $action=get_param_raw('action','default');
    }
	$module=get_param_raw('module','clients');
	$user->DenyInauthorized();

    if(in_array($user->Get("user"), array("onlime_all", "onlime", "onlime2")))
        $user->Logout();

	$design->assign_by_ref('authuser',$user->_Data);
	if (!($fixclient=$user->GetAsClient())){
		$fixclient=get_param_protected('clients_client','');
		$uri='';
		$design->assign_by_ref('request_uri',$uri);
		$unfix=get_param_protected('unfix',0);
		if ($unfix) {
			if (get_param_protected('clients_client','',false)) {
				$_SESSION['clients_client'] = $fixclient;
			} else{
				$module_clients->client_unfix();
				$fixclient='';
			}
		}
	}
/*
	if ($module!='clients') {
		session_write_close();
		$GLOBALS['SessionWriteClosed']=1;
	}
*/
    if($user->Get("user") == "nbn")
{
        $user->Logout();
}

	$design->assign_by_ref('fixclient',$fixclient);
	$fixclient_data=array(); $design->assign_by_ref('fixclient_data',$fixclient_data);
	$design->assign('module', $module);

	$design->AddMain('errors.tpl');
	if (isset($module_clients) && $module!="clients" && $fixclient) $fixclient_data=$module_clients->get_client_info($fixclient);
	$modules->GetMain($module,$action,$fixclient);
	if ($module=="clients" && $fixclient) $fixclient_data=$module_clients->get_client_info($fixclient);
	if ($fixclient) $fixclient_data=$module_clients->get_client_info($fixclient);
	$uri=str_replace('/&','/?',str_replace('&id='.$fixclient,'',$_SERVER['REQUEST_URI'])."&unfix=1");
	$module_clients->clients_headers();
	$design->AddTop('search.tpl');
	$modules->GetPanels($fixclient);
	header('Content-Type: text/html; charset=utf-8');
	$design->Process();
?>
