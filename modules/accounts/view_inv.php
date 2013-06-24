<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include INCLUDE_ARCHAIC_PATH."lib.php";

	$invoice_no="";
	$code=get_param_raw('code');
	$design->assign('code',$code);
	$img=get_param_raw('img');

	if ($code) {
		$invoice_no=udata_decode($code);
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
			readfile(IMAGES_PATH.'stamp.gif');
		}
		exit;
	}

	$invoice_no=get_param_protected("invoice_no",$invoice_no);
	if ($invoice_no=="") die("Не определен номер счета-фактуры");

	$query="SELECT * from bill_invoices WHERE invoice_no='$invoice_no'";
	$db->Query($query);
	if (!($row=$db->NextRecord())) exit;
	$row=round_dig($row);

	$sum_plus_tax_in_words=spell_number($row['sum_plus_tax'],"RUR");
	$design->assign("sum_plus_tax_in_words",$sum_plus_tax_in_words);
	$tax_sum_in_words=spell_number($row['tax_sum'],"RUR");
	$design->assign("tax_sum_in_words",$tax_sum_in_words);

// проверяем даты до конвертации	
	$invoice_date=$row['invoice_date'];
	$pay_date=$row['pay_date'];
	$pay_no=$row['pay_no'];
	$print_pay=true;
//	echo "$pay_date > $invoice_date <br>";
	if (($pay_date>$invoice_date) or ($pay_no == "Платеж из переплаты")) $print_pay=false;
	$design->assign("print_pay",$print_pay);
	

	$row['invoice_date']=convert_date($row['invoice_date']);
	$row['pay_date']=convert_date($row['pay_date']);

	$design->assign("invoice",$row);
/*
	$invoice_date=$row['invoice_date'];
	$pay_date=$row['pay_date'];
	$print_pay=true;
	echo "$pay_date > $invoice_date <br>";
	if ($pay_date>$invoice_date) $print_pay=false;
	$design->assign("print_pay",$print_pay);
*/	 

	$client=$row['client'];

	$query="SELECT * from clients WHERE client='$client'";
	$db->Query($query);
	$row=$db->NextRecord();
	$row['contract_date']=convert_date($row['contract_date']);
/*
//**********************************
// Всегда печатаем счета от МСН
	$row['firma']='mcn';
//***************************
*/
	$design->assign("client",$row);
	$z=false;//флаг означающий что это залог 
	$query="SELECT * from bill_invoice_lines where invoice_no='$invoice_no' and not (item like '*Всего%') ORDER BY line ";
	$db->Query($query);
	$modem=array();
	$kol=array();
	while ($row=$db->NextRecord()){
		$row=round_dig($row);
		$lines[]=$row;
		if (strpos($row['item'],'Залог')!==false){ 
			$z=true;
			$modem[]=trim(substr($row['item'],8,strlen($row['item'])));
			$kol[]=$row['amount'];

		};
	};

	$design->assign("rows",$lines);
	$design->assign('zalog',$z);
	$design->assign("modem",$modem);
	$design->assign("k",$kol);
	$todo=get_param_protected("todo","akt");
	switch ($todo)
	{
		case "invoice": $design->display("accounts/invoice_rub.tpl"); break;
		case "akt": 
			if($z){$design->display("accounts/invoice_akt_rub_zalog.tpl"); break; }
			$design->display("accounts/invoice_akt_rub.tpl"); break;
	};

?>