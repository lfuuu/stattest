<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include  "../../include_archaic/lib.php";
	require_once("make_inv.php");
//аутентификация
	$module=get_param_raw('module','');
	$action=get_param_raw('action','default');
	$user->DoAction($action);
	$user->DenyInauthorized();

	$client=get_param_protected('clients_client','');
	if ($client=="") 
	{
		echo("Не выбран клиент");
		exit;
		
	};
	$design->assign('client',$client);
	$todo=get_param_protected('todo');
	$message='';
	$bill_selected=get_param_protected("bill_no");	
	$design->assign("bill_selected",$bill_selected);
	$saldo=get_param_protected('saldo');	
	switch ($todo){
	case "add_payment":
			$pay_sum=get_param_protected('pay_sum');
			$pay_pp=get_param_protected('pay_pp');
			$pay_date=get_param_protected('pay_date');
			$bill=get_param_protected('bill');
			$flag=get_param_protected('flag');
			$type=get_param_protected('type');
			$saldo=get_param_protected('saldo');
			$comment=get_param_protected("comment");
			
			$db->Connect();
			
			$db->Query("SELECT * from bill_currency_rate where date='$pay_date'");
			
			if(!($r=$db->NextRecord())){
				$message="На <b>$pay_date</b> не установлен курс доллара.
				Устновите его <a href='../../index.php?modules=accounts&action=accounts_add_usd_rate' target='_blank'>здесь</a> и внесите платеж еще раз.";
				break;
				
			}
			$sum_usd=$pay_sum/$r['rate'];
			$rate=$r['rate'];
			$query="SELECT * from bill_bills where bill_no='$bill'";
			$db->Query($query);
			if (!$r=$db->NextRecord())
			{
				$message="Ошибка при обращении к базе $query <br>".mysql_error()."<br>";
				break;
			};
			
			$delta_pr=abs($r['sum']-$sum_usd)/$r['sum']*100;
			$delta=abs($r['sum']-$sum_usd);
//			echo "платеж-$sum_usd счет-{$r['sum']} saldo-$saldo delta_pr=$delta_pr flag=$flag<br> ";
			
			if ($delta_pr<=3 && ($r['sum']!=0)){
				// платеж отличается от суммы меньше чем 3% 
//			echo "<br>Платеж отличается меньше чем на 3 %<br>";	
				$bill_sum=$r['sum'];
				$pay_rate=$pay_sum/$bill_sum;
				$sum_usd=$bill_sum;
				make_balance_correction_db($client,$bill_sum);
				$query = "insert into bill_payments (client,payment_no, payment_date, sum_rub, sum_usd,rate,bill_no,type,comment) 
					  values ('$client','$pay_pp','$pay_date','$pay_sum','$bill_sum','$pay_rate','$bill','$type','$comment')";
				$db->Query($query);
				$message="Платеж на сумму <b>$pay_sum</b> по счету &#035; <b>$bill</b>внесен. ";
				if ($db->mErrno>0) $message="Внести платеж не удалось. Ошибка с базой<br>".mysql_error();
			
				make_invoice($pay_pp,$pay_sum,$sum_usd,$pay_rate,$bill,$pay_date);
				break;
			}
			// переплата 
			
//			echo "<br>переплата".(($sum_usd-$r['sum'])*100/$r['sum'])."<br>";
			if (
					((($sum_usd-$r['sum'])*100/$r['sum']>3) && ($flag =="1"))
				|| ($r['sum']==0)
				){
//			echo "переплата<br>";
				$message="Сумма платежа отличается от суммы счета более чем на 3%, <b>переплата</b> составляет <b>".abs($sum_usd-$r['sum'])."</b><br>";
				
				make_balance_correction_db($client,$sum_usd);
				$query = "insert into bill_payments (client,payment_no, payment_date, sum_rub, sum_usd,rate,bill_no,type,comment) values ('$client','$pay_pp','$pay_date',$pay_sum,$sum_usd,$rate,'$bill',$type,'$comment')";
				$db->Query($query);
				$message="Платеж на сумму <b>$pay_sum</b> по счету &#035; <b>$bill</b>внесен. ";
				if ($db->mErrno>0) $message="Внести платеж не удалось. Ошибка с базой<br>".mysql_error();
				
				$query="UPDATE saldo set saldo=saldo+$delta where client='$client'";
				$db->Query($query);
				make_invoice($pay_pp,$pay_sum,$sum_usd,$pay_rate,$bill,$pay_date);
				break;
			};
			// недоплата но есть положительное сальдо которое покрывает недоплату
/*			printdbg ($sum_usd,"sum_usd");
			printdbg($saldo,'saldo');
			printdbg($r['sum'], 'Bill_sum');
			printdbg(($sum_usd+$saldo-$r['sum']),'если учитывать сальдо');
			printdbg((($r['sum']-$sum_usd-$saldo)/$r['sum']),"% esli i saldo ne pokrivaet");
*/
			if(($sum_usd+$saldo-$r['sum']>0) or 
				((($r['sum']-$sum_usd-$saldo)/$r['sum'])<1.03) ){
//				echo "недоплата но сальдо хватает чтоб провести этот счет<br>";
				make_balance_correction_db($client,$sum_usd);
				$query = "insert into bill_payments 
					(client,payment_no, payment_date, sum_rub, sum_usd,rate,bill_no,type,comment) 
					values ('$client','$pay_pp','$pay_date',$pay_sum,$sum_usd,$rate,'$bill',$type,'$comment')";
				$db->Query($query);

				$message="Платеж на сумму <b>$pay_sum</b> по счету &#035; <b>$bill</b>внесен. ";
				if ($db->mErrno>0) $message="Внести платеж не удалось. Ошибка с базой<br>".mysql_error();
				
				$delta_saldo=$r['sum']-$sum_usd;
				$saldo-=$delta_saldo;
				if ($saldo<0) $saldo=0;
				$query="UPDATE saldo set saldo=$saldo where client='$client'";
				$db->Query($query);
				
				$pay_sum=$r['sum']*$rate;
				//$pay_rate=$r
/*				printdbg($pay_sum,'pay_sum_rubl');
				printdbg($sum_usd,'sum_usd');
*/
				//printdbf
				//make_invoice($pay_pp,$pay_sum,$sum_usd,$pay_rate,$bill);
				break;
				
				
			}
			//недоплата и сальдо не хватает чтоб провести счет
			if ($sum_usd+$saldo-$r['sum']<0){
				make_balance_correction_db($client,$sum_usd);
				$query = "insert into bill_payments (client,payment_no, payment_date, sum_rub, sum_usd,rate,bill_no,type,comment) values ('$client','$pay_pp','$pay_date',$pay_sum,$sum_usd,$rate,'$bill',$type,'$comment')";
				$db->Query($query);
				$message="Платеж на сумму <b>$pay_sum</b> по счету &#035; <b>$bill</b>внесен. ";
				if ($db->mErrno>0) $message="Внести платеж не удалось. Ошибка с базой<br>".mysql_error();
				$query="UPDATE saldo set saldo=saldo+$sum_usd where client='$client'";
				$db->Query($query);
				break;
				
			}
			
			
			
		
		break;
		
	default:
		
		break;
	}
		
	$query="SELECT * from bill_bills where client='$client' and state = 'ready' or state='sent' ";
	$db->Query($query);
	$rows=array();
	while($r=$db->NextRecord())
	{
		$r['bill_date']=convert_date($r['bill_date']);
		$rows[]=$r;
		
	};
	$design->assign('saldo',$saldo);
	$design->assign("bills",$rows);
	$now=date("Y-m-d");
	$design->assign("now",$now);
		
		
	$design->assign("error_message",$message);
	$design->display("accounts/add_payment.tpl");




?>



