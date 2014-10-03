<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";
	
//аутентификация
	$module=get_param_raw('module', 'users');
	$action=get_param_raw('action',$module.'_default');
	$user->DoAction($action);
	$user->DenyInauthorized();
	$design->assign_by_ref('authuser',$user->_Data);
	if (!access('services_additional','edit')) exit;	
	
	$design->display('errors.tpl');
	$id=get_param_protected('id');
	$dbmap=new Db_map_nispd();	

	$R=array(''); $db->Query('select * from bill_monthlyadd_reference');
	while ($r=$db->NextRecord()) $R[]=$r;
	$design->assign('copy',$R);

	if ($dbmap->ApplyChanges()=="ok") {
		$design->display('pop_header.tpl');
		$design->display('reload_parent.tpl');
		$design->display('pop_footer.tpl');
		exit;
	}
	$dbmap->ShowEditForm('bill_monthlyadd','bill_monthlyadd.id='.$id,array(),1,!access("services_additional","full"));
	$design->assign('id',$id);
	$hl=get_param_raw('hl');
	$design->assign('hl',$hl);
	$design->display('pop_header.tpl');
	$design->display('services/db_edit_ad.tpl');
	$design->display('pop_footer.tpl');
?>