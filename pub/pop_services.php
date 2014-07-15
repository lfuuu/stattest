<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";

	$action=get_param_raw('action');
	$user->DoAction($action); $user->DenyInauthorized();

	$table=get_param_raw('table'); if (!$table) return;
	$id=get_param_integer('id'); if (!$id) return;
	$hl=get_param_raw('hl'); 

    switch($table) {
        case 'usage_ip_ports': 
            if (!access('services_internet','edit') && !access('services_collocation','edit')) return;
            break;
        case 'usage_ip_routes':
            if (!access('services_internet','edit') && !access('services_collocation','edit')) return;
            break;
        case 'usage_voip':
            if (!access('services_voip','edit')) return;
            break;
        case 'domains':
            if (!access('services_hosting','edit')) return;
            break;
        case 'usage_ip_ppp':
            if (!access('services_ppp','edit')) return;
            break;
        case 'bill_monthlyadd':
        case 'usage_extra':
            if (!access('services_additional','edit')) return;
            break;
        case 'emails':
            if (!access('services_mail','edit')) return;
            break;
        case 'usage_welltime': 
            if (!access('services_welltime','full')) return;
            break;
        case 'usage_virtpbx': 
            if (!access('services_welltime','full')) return;
            break;
        case 'usage_8800': 
            if (!access('services_welltime','full')) return;
            break;
        case 'usage_sms': 
            if (!access('services_welltime','full')) return;
            break;
        default: return;
    }


	$design->assign('hl',$hl);	
	$dbf = DbFormFactory::Create($table);
	if (!$dbf) return;
	if (!$dbf->Load($id)) return;
	$ret = $dbf->Process();
	if (false && $ret=='edit') {
		header('Location: ?table='.$table.'&id='.$id);
		exit;
	}
	$design->display('pop_header.tpl');
	$dbf->nodesign=1;
	HelpDbForm::assign_log_history($table,$id);
	$dbf->Display(array('table'=>$table,'id'=>$id),$table,'Редактирование'.' id='.$id);
	$design->display('errors.tpl');
	$design->display('dbform.tpl');
	$design->display('errors.tpl');
	$design->display('pop_footer.tpl');
?>
