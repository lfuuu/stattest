<?php
	define("PATH_TO_ROOT",'../../stat/');
	include PATH_TO_ROOT."conf.php";
	
//аутентификация
	$module=get_param_raw('module', 'users');
	$action=get_param_raw('action',$module.'_default');
	$user->DoAction($action);
	$user->DenyInauthorized();
	$design->assign_by_ref('authuser',$user->_Data);
	if (!access('services_mail','edit')) exit;	

	$hl=get_param_raw('hl'); $design->assign('hl',$hl);
	$design->display('errors.tpl');
	$id=get_param_protected('id');
	$dbmap=new Db_map_nispd();
	if ($dbmap->ApplyChanges()=="ok") {
		$design->display('pop_header.tpl');
		$design->display('reload_parent.tpl');
		$design->display('pop_footer.tpl');
		exit;
	}
	$dbmap->ShowEditForm('emails','emails.id='.$id,array(),1,!access("services_mail","full"));
	$design->assign('id',$id);
	$hl=get_param_raw('hl');
	$design->assign('hl',$hl);

	$r=$dbmap->SelectRow('emails','id='.$id);

	$R=array('mcn.ru'); $db->Query('select domain from domains where client="'.$r['client'].'"');
	while ($r=$db->NextRecord()) $R[]=$r['domain'];
	$design->assign('domains',$R);
		
	$design->display('pop_header.tpl');
	$design->display('services/db_edit_mail.tpl');
	$design->display('pop_footer.tpl');
	
?>