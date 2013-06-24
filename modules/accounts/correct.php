<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";

	function correct($sum1,$sum2,$sum3,&$bill,$cr1=1,$cr2=1,$addq=' AND (l.amount=1) AND (l.price>0)') {
		global $db,$bill_no,$client;
		$sum1=round($sum1,4);
		$sum2=round($sum2,4);
		$sum3=round($sum3,4);
		if ($sum1>=0) $sum1='+'.$sum1;
		if ($sum2>=0) $sum2='+'.$sum2;
		if ($sum3>=0) $sum3='+'.$sum3;
		$query="SELECT l.* from bill_invoice_lines as l
				LEFT JOIN bill_invoices as i ON l.invoice_no=i.invoice_no
				WHERE (i.client='{$client}') AND (i.bill_no='{$bill_no}') AND (abs(l.price)>abs({$sum2})*100) ".$addq."
				ORDER BY l.sum DESC LIMIT 1";
		$db->Query($query);
		if ($r=$db->NextRecord()){
			$query="UPDATE bill_invoice_lines SET price=price{$sum1},sum=sum{$sum1},sum_plus_tax=sum_plus_tax{$sum2},tax_sum=tax_sum{$sum3} WHERE (invoice_no='{$r['invoice_no']}') AND (line={$r['line']})";
			$db->Query($query);
			if ($cr2) {
				$query="UPDATE bill_invoices SET sum=sum{$sum1},sum_plus_tax=sum_plus_tax{$sum2},tax_sum=tax_sum{$sum3} WHERE (invoice_no='{$r['invoice_no']}') AND (client='{$client}')";
				$db->Query($query);
			}
			echo "Счёт откорректирован<br>";
		} else if ($cr1){
			$sum=$sum2;
			$query="SELECT * from bill_invoices where (invoice_no LIKE '{$bill_no}-%') AND (client='$client')";
			$db->Query($query);
			$R=array();
			$max=0;
			while ($r=$db->NextRecord()){
				$max=max($max,substr($r['invoice_no'],strlen($r['invoice_no'])-1,1));
				$v=$r;
			}
			$v['invoice_no']=$bill_no.'-'.($max+1);
			unset($v['invoice_date']);
			$v['tax_sum']=0; $v['sum']=$sum; $v['sum_plus_tax']=$sum;
			$v['tax_sum_usd']=0; $v['sum_usd']=0; $v['sum_plus_tax_usd']=0;
			foreach ($v as $k=>$val) {if (is_int($k)) unset($v[$k]); else $v[$k]=addslashes($val);}
			$query="INSERT INTO bill_invoices (invoice_date,".implode(",",array_keys($v)).") VALUES (NOW(),'".implode("','",array_values($v))."')";
			$db->Query($query);
			$query="INSERT INTO bill_invoice_lines (invoice_no,line,item,ediz,amount,price,sum,tax,tax_sum,sum_plus_tax,price_usd,sum_usd,tax_sum_usd,sum_plus_tax_usd) ".
							"VALUES ('{$v['invoice_no']}',1,'Суммовая разница','??.',1,$sum,$sum,0,0,$sum,0,0,0,0)";
			$db->Query($query);
			echo "Создана дополнительная счёт-фактура<br>";	
		} else echo "Не скоррктировано расхождение в копейках<br>";
	}
	
	$action=get_param_raw('action','default');
	$user->DoAction($action);
	$user->DenyInauthorized();

	$bill_no=get_param_protected("bill_no");
	$client=get_param_protected("client");
	$sum=floatval(get_param_protected("sum"));
	if (!$bill_no || !$client) die("Не определен счет");

	$db->Query("SELECT * from bill_bills where bill_no='{$bill_no}' and client='{$client}'");
	$bill=$db->NextRecord();

	$sum=-floatval($sum);
	$sum1=($sum*100/(100.0+$r['tax']));
	$sum2=$sum;
	$sum3=($sum*$r['tax']/(100.0+$r['tax']));
	if ($sum2) correct($sum1,$sum2,$sum3,$bill,$cr);

	$query="SELECT *,(round(sum,2)) as sum,(round(tax_sum,2)) as tax_sum,(round( sum_plus_tax,2)) as sum_plus_tax FROM bill_invoices WHERE (bill_no='{$bill_no}') AND (client='{$client}')";
	$db->Query($query);
	$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
	foreach ($R as $inv) {
		$query="SELECT sum(round(sum,2)) as sum,sum(round( tax_sum,2)) as tax_sum,sum(round( sum_plus_tax,2)) as sum_plus_tax
							FROM bill_invoice_lines WHERE (invoice_no='{$inv['invoice_no']}') AND (amount!=0)";
		$db->Query($query);
		if ($invs=$db->NextRecord()) {
//		echo "<pre>";
//		print_R($invs); print_r($inv); exit;
			$sum1=$inv['sum']-$invs['sum'];
			$sum2=$inv['sum_plus_tax']-$invs['sum_plus_tax'];
			$sum3=$inv['tax_sum']-$invs['tax_sum'];
			if ($sum1!=0 || $sum2!=0 || $sum3!=0) correct($sum1,$sum2,$sum3,$bill,0,0," AND (i.invoice_no='{$inv['invoice_no']}')");
		}
	}

?>
