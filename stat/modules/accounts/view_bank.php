<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include INCLUDE_ARCHAIC_PATH."lib.php";
	$module=get_param_raw('module','');
	$action=get_param_raw('action','default');
	$user->DoAction($action);
	$user->DenyInauthorized();
	
	
	$script_name=$_SERVER['SCRIPT_NAME'];
	$dir=dirname($script_name);
	$path=$_SERVER['DOCUMENT_ROOT'].$dir;
	$file="$path/1c/".get_param_protected("file");
	if (file_exists($file)){
		$f=fopen($file,'r');
	//	echo "have opened file<br>";
	}else {
		echo "file dont find $file<br>";
		exit;
	}
	
	$bank=fgets($f);
	printdbg($bank);
	if (stripos($bank,"1CClientBankExchange")!==false){
	//Маркомнет
	printdbg("Markomnet");
	$payments=array();
	while (!feof($f)){
		$line=fgets($f);
		$line=convert_cyr_string($line,'w','k');
		if ((stripos($line,"Платежное поручение") !==false) ){
			//echo "$line<br>";
			$pay=array();
			while (!feof($f) and ((stripos($line,"КонецДокумента")===false) )){
				$line=fgets($f);
				$line=convert_cyr_string($line,'w','k');
				//echo "$line<br>";
				$l_=explode("=",$line);
				$pay["{$l_[0]}"]=$l_[1];
			}
			$payments[]=$pay;
		};
		
	
	}
	fclose($f);
//printdbg($payments,"payments");	
foreach ($payments as $pay){
	
	$sum_rub=$pay['Сумма'];
	$payment_date=$pay['Дата'];
	$payment_pp=$pay['Номер'];
	$comments=$pay['НазначениеПлатежа'];
	
	$company=$pay['Плательщик1'];
	$client=find_client($pay['ПлательщикИНН']);
	$inn=$pay['ПлательщикИНН'];
	$bill_no=find_bill($pay['НазначениеПлатежа']);
	
	$valid=true;
	// проверяем есть ли такой счет заданного клиента и не оплачен ли он
	if(!validate($bill_no,$client)) $valid=false;
	$other_bills=get_bills($client);
	
	
	// преобразование даты
	$p_=explode('.',$payment_date);
	$payment_date=substr($p_[2],0,4)."-$p_[1]-$p_[0]";
		
	$end_pay=array(	'company'=>$company,
			"client"=>$client,
			"sum_rub"=>$sum_rub,
			'payment_date'=>$payment_date,
			'payment_pp'=>$payment_pp,
			'type'=>0,
			'bill_no'=>$bill_no,
			'valid'=>$valid,
			'bills'=>$other_bills,
			'comments'=>$comments,
			'inn'=>$inn);
	if (stripos($company,"МАРКОМНЕТ")===false) $end_paymnets[]=$end_pay;
	
}
}elseif(stripos($bank,'$OPERS_LIST')!==false){
// MCN
	$payments=array();
	while (!feof($f)){
		$line=fgets($f);
		$line=convert_cyr_string($line,'w','k');
		if ((stripos($line,'$OPERATION') !==false) ){
			//echo "$line<br>";
			$pay=array();
			while (!feof($f) and ((stripos($line,'$OPERATION_END')===false) )){
				$line=fgets($f);
				$line=convert_cyr_string($line,'w','k');
				//echo "$line<br>";
				$l_=explode("=",$line);
				$pay["{$l_[0]}"]=$l_[1];
			}
			$payments[]=$pay;
		};
		
	
	}
	fclose($f);
//printdbg($payments,"payments");	
foreach ($payments as $pay){
	
	$sum_rub=$pay['RUR_AMOUNT'];
	$payment_date=$pay['DOC_DATE'];
	$payment_pp=$pay['DOC_NUM'];
	$comments=$pay['OPER_DETAILS'];
	
	$company=$pay['CORR_NAME'];
	$client=find_client($pay['CORR_INN']);
	$inn=$pay['CORR_INN'];
	$bill_no=find_bill($pay['OPER_DETAILS']);
	
	$valid=true;
	// проверяем есть ли такой счет заданного клиента и не оплачен ли он
	if(!validate($bill_no,$client)) $valid=false;
	$other_bills=get_bills($client);
	
	
	// преобразование даты
	$p_=explode('.',$payment_date);
	$payment_date=substr($p_[2],0,4)."-$p_[1]-$p_[0]";
		
	$end_pay=array(	'company'=>$company,
			"client"=>$client,
			"sum_rub"=>$sum_rub,
			'payment_date'=>$payment_date,
			'payment_pp'=>$payment_pp,
			'type'=>0,
			'bill_no'=>$bill_no,
			'valid'=>$valid,
			'bills'=>$other_bills,
			'comments'=>$comments,
			'inn'=>$inn);
	if (stripos($company,"МАРКОМНЕТ")===false) $end_paymnets[]=$end_pay;

}
}
//printdbg($end_paymnets);

$design->assign('payments',$end_paymnets);
$design->display("accounts/auto_pays.tpl");


//printdbg($end_paymnets,"платежи");

function validate($bill_no,$client){
	GLOBAL $db;
	$db->Connect();
	$query="SELECT  state 
		from bill_bills 
		where 	client='$client' 
			and bill_no='$bill_no'";
	$db->Query($query);
	
	if ($db->NumRows()==0) return false;
	$b=$db->NextRecord();
	if ($b['state']!="ready") return false;
	return true;
	
	
};

function get_bills($client){
	GLOBAL $db;
	$db->Connect();
	$query="SELECT bill_no 
		from bill_bills 
		where client='$client' and state='ready'";
	$db->Query($query);
	if ($db->NumRows()==0) return array();
	$bills=array();
	While($r=$db->NextRecord()){
		$bills[]=$r['bill_no'];
	}
	
	return $bills;
}

function find_client($inn){
	Global $db;
	$db->Connect();
	$query="SELECT client FROM clients WHERE inn='$inn'";
	$db->Query($query);
	if (($db->mErrno > 0) or ($db->NumRows()==0)) return false;
	$r=$db->NextRecord();
	$client=$r['client'];
	if ($client=='') return false;
	return $client;
}	

function find_bill($m){
// получает строку "назначение платежа" иногда там есть номер счета
//echo $m."<br>";
$reg="|20\d{4}-\d{1,4}|";
$out=array();
preg_match($reg,$m,$out);

//printdbg($out,'output');
return $out[0];


}

?>
