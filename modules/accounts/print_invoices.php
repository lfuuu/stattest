<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include INCLUDE_ARCHAIC_PATH."lib.php";
	$module=get_param_raw('module','');
	$action=get_param_raw('action','default');
	$user->DoAction($action);
	$user->DenyInauthorized();
	
	
	//определяем период
	$todo=get_param_protected('todo');
	
	$start=get_param_protected('start',date("Y-m-d"));
	$finish=get_param_protected('finish',date("Y-m-d"));
	
	// получаем все платежи за данный период и все счета 
	$query="SELECT i.invoice_no as no, i.client as client from bill_invoices as i, bill_payments as p
	where p.payment_date >='$start' and p.payment_date<='$finish'
	and i.bill_no=p.bill_no 
	and i.sum_plus_tax>0";
	//printdbg($query);
	
	$db->Query($query);
	$invoices=array();
	$rows='';
	$clients=array();
	
	while($r=$db->NextRecord()){
		$invoices[]=$r;
		$rows.="15%,15%,";
		$clients[]=$r['client'];
		
	};
	
	$clients=array_unique($clients);
	
	$rows=substr($rows,1,strlen($rows));
//	printdbg($invoices);
	$design->assign('rows',$rows);
	$design->assign('invoices',$invoices);
	switch ($todo){
		case "print_invoices":
			$design->display("accounts/invoices.tpl");
			break;
		case "print_akt":
			$design->display("accounts/akt.tpl");
			break;
		case "print_envelopes":
			$design->assign('clients',$clients);
			$design->display('accounts/envelope.tpl');
			break;
			
			
		
	}
	

?>
