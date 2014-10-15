<?php
	define("PATH_TO_ROOT",'../../stat/');
    include PATH_TO_ROOT."conf_yii.php";

    $user->AuthorizeByUserId(Yii::$app->user->id);

//аутентификация
	$module=get_param_raw('module', 'users');
	$action=get_param_raw('action',$module.'_default');

	$design->assign_by_ref('authuser',$user->_Data);
	if (!access('services_ppp','edit')) exit;	

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
	$dbmap->ShowEditForm('usage_ip_ppp','usage_ip_ppp.id='.$id,array(),1,!access("services_ppp","full"));
	$design->assign('id',$id);
	$hl=get_param_raw('hl');
	$design->assign('hl',$hl);

	$r=$dbmap->SelectRow('usage_ip_ppp','id='.$id);

	$R=array('id'=>0); $db->Query('select * from usage_ip_ports where client="'.$r['client'].'"');
	while ($r=$db->NextRecord()) $R[$r['id']]=$r;
	$design->assign('ports',$R);
		
	$design->display('pop_header.tpl');
	$design->display('services/db_edit_ppp.tpl');
	$design->display('pop_footer.tpl');
	
?>