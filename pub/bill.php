<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";
	if (!($R=udata_decode_arr(get_param_raw('bill')))) return;
	if (!$R['client'] || !$R['bill']) return;
	if (!$db->QuerySelectRow('newbills',array('bill_no'=>$R['bill'],'client_id'=>$R['client']))) return;
	$_GET=$R;
	$db->Query('update newbill_send set state="viewed" where bill_no="'.$R['bill'].'"');
	if(isset($_REQUEST['dbg']))
		$design->assign('dbg',true);
	else
		$design->assign('dbg',false);
	$design->assign('emailed',$v=get_param_raw('emailed',1));
	$module_newaccounts->newaccounts_bill_print('');
	header('Content-Type: text/html; charset=koi8-r');
	$design->Process();
?>
