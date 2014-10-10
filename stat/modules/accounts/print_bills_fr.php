<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include INCLUDE_ARCHAIC_PATH."lib.php";


	
	$type=get_param_protected('t');
	if ($type=='') exit;
	$design->assign('type',$type);
	$limit_from=get_param_protected('limit_from',0);
	/*	
	all, 
	*/
	$now=date("Y-m")."-01";
	//$req="SELECT * from bill_log_auto  order by id desc limit 1";
	$req="SELECT b.bill_no as bill from bill_bills as b, clients as c   
		where b.bill_date>='$now' 
		AND c.client=b.client 
		AND c.manager='$type'
		AND b.state='ready'
		order by b.client
		LIMIT $limit_from,90";
	//printdbg($req);
	
$db->Connect();
	$db->Query($req);
	$array_bills=array();
	$rows="";
	while($r=$db->NextRecord()){
		$array_bills[]=$r['bill'];
		$rows.="10%,";
	}
	/*$auto_bills=$db->NextRecord();
	$list_bills=$auto_bills[$type];
	

	$array_bills=explode(',',$list_bills);	
	$num=count($array_bills);
	
	for ($i=1;$i<=$num;$i++){
		$rows.="10%,";
	}*/
	//printdbg($array_bills);
	$rows=substr($rows,0,strlen($rows)-1);
	
	$design->assign('rows',$rows);
	$design->assign('bills',$array_bills);
	$design->display('accounts/print_bills_fr.tpl');
	
	
	

?>
