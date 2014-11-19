<?php
die('This function marked for deleting');
error_reporting(E_ALL);
	
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include  "../../include_archaic/lib.php";
	require_once("make_inv.php");
	

//аутентификация
	$module=get_param_raw('module','clients');
	$action=get_param_raw('action','default');
	$user->DoAction($action); if ($action=='login') $action='default';
	
	$user->DenyInauthorized();
	$design->assign_by_ref('authuser',$user->_Data);
	
	$client=get_param_protected('client');
	$sum_rub=get_param_protected("sum_rub");
	$payment_pp=get_param_protected('pay_pp');
	$payment_date=get_param_protected('pay_date');
	$bill_no=get_param_protected('bill_no');
	
	if (!validate($bill_no,$client)) {
		?>
			<a href="../../index.php?module=accounts&action=accounts_payments&clients_client=<?=$client;?>" target="_blank">
			Платежи клиента</a><br>
		<?php
		die("Не верная пара $client - $bill_no<br>");
	};
	// получаем сумму по счету 
	$query="SELECT * from bill_bills where bill_no='$bill_no'";
	$db->Connect();
	$db->Query($query);
	$r=$db->NextRecord();
	$bill_sum=$r['sum'];
	printdbg($bill_sum,"bill_sum");

	// получаем курс на день платежа
	$db->Query("SELECT * from bill_currency_rate where date='$payment_date'");
	if(!($r=$db->NextRecord())){
		echo "На <b>$pay_date</b> не установлен курс доллара.
		Устновите его <a href='../../index.php?module=accounts&action=accounts_add_usd_rate' target='_blank'>здесь</a> и внесите платеж еще раз.";
		echo "SELECT * from bill_currency_rate where date='$payment_date'"; 
		exit;
	}
	$rate_day=$r['rate'];
	if ($bill_sum==0) $delta=1000; else $delta=round(abs((($sum_rub/$rate_day)-$bill_sum))/$bill_sum,3);
		
	if ($delta<0.03 ){
		$real_rate=$sum_rub/$bill_sum;
		// вносим платеж
		make_balance_correction_db($client,$bill_sum);
		$query = "insert into bill_payments 
			(client,payment_no, payment_date, sum_rub, sum_usd,rate,bill_no,type) 
			values ('$client','$payment_pp','$payment_date',
				$sum_rub,$bill_sum,$real_rate,'$bill_no',0)";
		$db->Query($query);
		if ($db->$mErrno>0) die("Внести платеж не удалось. Ошибка с базой<br>".mysql_error());		
		echo "Платеж на сумму <b>$sum_rub</b> по счету &#035; <b>$bill_no</b>внесен. <br>";
		
		// делаем счета фактур
		make_invoice($payment_pp,$sum_rub,$bill_sum,$real_rate,$bill_no,$payment_date);
		?>
		<br>
		<a href="../../index.php?module=accounts&action=accounts_invoices&clients_client=<?=$client;?>" target="_blank">
			Счета фактур</a> подготовлены <br>
		<?php
		
		
	}else{
		$sum_usd=round($sum_rub/$rate_day,2);
		make_balance_correction_db($client,$sum_usd);
		$query = "insert into bill_payments 
			(client,payment_no, payment_date, sum_rub, sum_usd,rate,bill_no,type) 
			values ('$client','$payment_pp','$payment_date',
				$sum_rub,$sum_usd,$rate_day,'$bill_no',0)";
		$db->Query($query);
		if ($db->$mErrno>0) die("Внести платеж не удалось. Ошибка с базой<br>".mysql_error());		
		echo "Платеж на сумму <b>$sum_rub</b> по счету &#035; <b>$bill_no</b>внесен. <br>";
		
		echo "<h2>Платеж отличается от суммы счета более чем на 3 процента</h2>";
		?>
			
			<br>Счета фактур по данному платежу не выставлялись.
			<br> Провести платеж вы можете 
			<a href="../../index.php?module=accounts&action=accounts_payments&clients_client=<?=$client;?>" target="_blank">
			здесь</a><br>
		<?php


	}

	// 


?>
	
	<a href='javascript:self.close();'>закрыть окно</a>
<?php	



	
	








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
