<?php
die('This function marked for deleting');
	error_reporting(E_ALL);
	require_once("../../include_archaic/lib.php");

//	define("PATH_TO_ROOT",'../../');
//	include "../../conf.php";
	require_once(INCLUDE_PATH.'util.php');
	require_once("make_inv.php");

//echo "<h1>make_inv2</h1><br>";	
db_open();
	
	$bill_no=get_param_protected('bill_no');
	if ($bill_no==''){
		echo "не определен номер счета";
		exit;
	};
	
	$client=get_param_protected('client');
	
	if ($client==''){
		echo "не определен номер счета";
		exit;
	};
//echo "bill='$bill_no' client=$client";
	
	$bill=$bill_no;
	$sum_pay_rub=$sum_pay_usd=0;
	$pays=array();
	
	$query="select * from bill_payments 
		where 	client='$client' 
			and bill_no='$bill_no' order by payment_date desc";
			
//	echo "query-$query<br>";
	
	$res1=mysql_query($query) or die(mysql_error());
	//echo "mysql_num_rows(res)=".mysql_num_rows($res1)."<br>";
	if (mysql_num_rows($res1)==0){
		$pay_pp="Платеж из переплаты";
//		echo "Платежей нет";
		$flag=false;
		$query="select * from bill_currency_rate where date=NOW()";
		$res=mysql_query($query) or die("нет курса доллара");
		if (mysql_num_rows($res)==0){die("нет курса доллара");};
		$r=mysql_fetch_array($res);
		$rate=$r['rate'];
		$rate_now=$rate;
		$sum_pay_rub=$sum_pay_usd=0;
//		echo "rate for today:".$rate."<br>";

		$query="insert into bill_payments 
			(client,payment_no, payment_date, sum_rub, sum_usd,rate,bill_no,type) 
			values ('$client','$pay_pp',NOW(),0,0,$rate,'$bill',2)";
		mysql_query($query) or die("Немогу внести платеж<br>".$query."<br>".mysql_error());
		
		$query="select * from bill_payments 
			where 	client='$client' 
			and bill_no='$bill_no' order by payment_date desc";
			
//	echo "query-$query<br>";
	
		$res1=mysql_query($query) or die(mysql_error());
			
	}
	if (mysql_num_rows($res1)>0){
//		echo "Платежи есть ";
		$flag=true;
		$pays=array();
		while (($r=mysql_fetch_array($res1))){
//			echo "вошли в цикл<br>";
			$sum_pay_rub+=$r['sum_rub'];
			$sum_pay_usd+=$r['sum_usd'];
			$pays[]=$r;
		}
		$pay_pp=$pays[0]['payment_no'];
		$pay_date=$pays[0]['payment_date'];
		if($sum_pay_rub==0 and $sum_pay_usd==0) {$rate=$rate_now;}else{ 
		$rate=round(($sum_pay_rub/$sum_pay_usd),4);};
		/*printdbg($pays,'pays');
		printdbg($sum_pay_rub,'sum_pay_rub');
		printdbg($rate,'rate');
		printdbg($sum_pay_usd,'sum_pay_usd');*/
		$pay_sum=$sum_pay_rub;
		$pay_sum_usd=$sum_pay_usd;

	}
	
	$query="select * from bill_bills where bill_no='$bill_no' and client='$client'";
	$res=mysql_query($query) or die(mysql_error());
	if (!($r=mysql_fetch_array($res))) die("Нет такого $bill_no счета для $client ");
	
	$bill_sum=$r['sum'];
	
	$query="select * from saldo where client='$client'";
	$res=mysql_query($query) or die(mysql_error());
	if (!($r=mysql_fetch_array($res))) die("сальдо для  $client ");
	$saldo=$r['saldo'];
	
//	echo "Saldo=$saldo<br>";
	
/*	
	echo "bill_sum-$bill_sum  sum_pay_usd-$sum_pay_usd<br>";
	echo "k=".(abs($bill_sum-$sum_pay_usd)/$bill_sum*100)."<br>";
	echo "k с сальдо".($sum_pay_usd-$bill_sum+$saldo)."<br>";
*/	
	if (abs($bill_sum-$sum_pay_usd)/$bill_sum*100<3){
		// платежи с точностью до 3 процентов равны сумме счета
//		echo "1.payments enough<br>";
//		echo "$bill_sum --- $sum_pay_usd<br>";
		$pay_sum=$sum_pay_rub;
		$rate=$pay_sum/$bill_sum;
		$sum_usd=$bill_sum;
		$pay_sum_usd=$sum_usd;
		
		
	}elseif(((abs($sum_pay_usd-$bill_sum+$saldo)/$bill_sum)<=1.03) or 
		($saldo>0 and $flag) or ($saldo>=$bill_sum)){
		// платим используя переплату 
		// вносим нулевой платеж 
		//mysql_query("Insert into bill_payments values(NULL,'п',NOW(),'$client','$bill',0,0,'0000-00-00',2)") or die(mysql_error());
		$delta=$bill_sum-$sum_pay_usd;
//		echo "payments enouph with saldo <br>старое сальдо $saldo берем -.$delta<br>";
		$saldo-=$delta;
		if (!$flag){
			$query="Update saldo set saldo=$saldo where client='$client'";
//			echo "изменяем сальдо $query";
			$res=mysql_query($query)or die(musql_error());
		}
		$pay_sum_usd=$bill_sum;
		$pay_sum+=$delta*$rate;
		//$sum_pay_rub=round($sum_pay_rub,2);
		$rate=$pay_sum/$pay_sum_usd;
//		echo "pay_sum_usd-$pay_sum_usd sum_pay_rub-$sum_pay_rub<br>";
	}else {
		echo "провести счет невозможно ";
		exit;
	}
/*	
printdbg($pay_pp,"pay_pp");
printdbg($pay_sum,"pay_sum");
printdbg($pay_sum_usd,"pay_sum_usd");
printdbg($rate,"rate");
printdbg($bill,"bill");
printdbg($pay_date,"pay_date");
*/

make_invoice($pay_pp,$pay_sum,$pay_sum_usd,$rate,$bill, $pay_date);

?>
