<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include INCLUDE_ARCHAIC_PATH."lib.php";

	$bill_no="";
	$bill_date="";
	$client="";
	$code=get_param_raw('code');
	$design->assign('code',$code);
	$img=get_param_raw('img');

	if ($code) {
		$D=udata_decode($code);
		list($bill_no,$bill_date,$client)=explode(',',$D);
		if ($img && ($_SERVER['HTTP_REFERER']!='http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?code='.$code)) exit;
	} else {
		$module=get_param_raw('module','');
		$action=get_param_raw('action','default');
		$user->DoAction($action);
		$user->DenyInauthorized();
	}
	
	if ($img) {
		header ('Content-type: image/gif');
		if ($img=='sign1') {
			readfile(IMAGES_PATH.'sign1.gif');
		} else if ($img=='sign2'){
			readfile(IMAGES_PATH.'sign2.gif');
		} else if ($img=='logo'){
			readfile(IMAGES_PATH.'logo2.gif');
		} else {
			readfile(IMAGES_PATH.'stampnew.gif');
		}
		exit;
	}
	
	$type=get_param_protected('t');
	if ($type=='') exit;
	$design->assign('type',$type);
	/*	
	all, 
	*/
	$req="SELECT * from bill_log_auto order by id desc limit 1";
	$db->Connect();
	$db->Query($req);
	$auto_bills=$db->NextRecord();
	$list_bills=$auto_bills[$type];

	$array_bills=explode(',',$list_bills);	
	$id=get_param_protected('i');

	if (!($bill_no=$array_bills[$id])) {
		echo "not such index $id <br>";
		exit;
	};
	
	$id++;
	$design->assign('id',$id);
	
	if ($bill_no=="") die("Не определен номер счета");
	
	$design->assign("bill_no",$bill_no);

	$query="SELECT bill_date, client from bill_bills where bill_no='$bill_no'";
	$db->Query($query);
	$drow=$db->NextRecord();
	$bill_date=$drow['bill_date'];
	
	$bill_date_=explode("-",$bill_date);
	$bill_date=$bill_date_[2].".".$bill_date_[1].".".$bill_date_[0];
	$design->assign("bill_date_f", $bill_date);

	$client=$drow['client'];
	if ($client=="") die("Не определен клиент");

	$query="SELECT * FROM bill_bill_lines WHERE bill_no='$bill_no' order by line";
	$db->Query($query);
	$lines=array();
	$totals=array();
	while ($row=$db->NextRecord()) {
		switch ($row['item']) {
			case "*Итого :": $totals[1]=$row;break;
			case "*НДС 18% :":$totals[2]=$row;break; 
			case "*Всего с НДС :": $totals[3]=$row; $total_sum=$row['sum'];break;
			default: $lines[]=$row;break;
		}
	}
	$currency="USD";

	$sum_in_words=spell_number($total_sum,$currency);
	$design->assign('sum_in_words',$sum_in_words);

	$design->assign("lines",$lines);
	$design->assign("totals",$totals);


	$query="SELECT firma, fax, company_full, address_post, usd_rate_percent, stamp, manager FROM clients WHERE client='$client'";
	$db->Query($query);
	if (!($row=$db->NextRecord())) exit;

	$design->assign('company_full',$row["company_full"]);
	$design->assign('fax',$row["fax"]);
	$design->assign('address_post',$row["address_post"]);
	$design->assign('usd_rate_percent',$row["usd_rate_percent"]);
	$design->assign('stamp',$row["stamp"]);
	$design->assign('manager',$row["manager"]);
	$design->assign("client",$client);
	

	switch ($row['firma']){
		case "markomnet":
			$design->display("accounts/markomnet_bill_print.tpl");
			break;
		case "mcn":
			$design->display("accounts/mcn_bill_print.tpl");
			break;
		default:
			$design->display("accounts/markomnet_bill_print.tpl");
			break;
	};
	$query="SELECT bill_date from bill_bills where bill_no='$bill_no'";
	$db->Query($query);
	$drow=$db->NextRecord();
	$bill_date=$drow['bill_date'];



?>
