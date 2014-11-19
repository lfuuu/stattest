<?php
die('This function marked for deleting');
    error_reporting(E_ALL);
    set_magic_quotes_runtime(0);
    include  "../../include_archaic/lib.php";
    include  "bill_make_lib.php";
	require_once(INCLUDE_PATH.'sql.php');
	$db		= new MySQLDatabase();
	require_once(INCLUDE_PATH.'writeoff.php');
	require_once(INCLUDE_PATH.'util_session_new.php');
	require_once(INCLUDE_PATH.'util.php');

    db_open();
    $client=$_GET['client'];
    if (strlen($client)<=0){
		echo "no client specified";
		exit;
    }
    if (get_param_raw('go')!=1) {
		echo "<form action='?go=1&client=".$client."' method=post>Размер задатка: <input type=text name=sum_virtual value='".SUM_ADVANCE."'>";
		echo "(он понадобится, если вы захотите потом отобразить счёт с пунктом 'за вычетом ранее оплаченного задатка')<br>";
		echo "<input type=submit value='выставить'></form><br>";
		return;
	}
	$sum_virtual=floatval(get_param_raw('sum_virtual'));
	$period=date("Y-m");
	$bill_date=date("Y-m-d");
    $bill_no=do_make_bill_generate_number(substr($period,0,4).substr($period,5,2));
	
	$sum=0;
	$bool=0;
    foreach ($writeoff_services as $service) {
		$db->Query("select A.* from $service as A  where (A.status = 'connecting') and (A.client='{$client}') group by A.id");
		$R=array();while ($r=$db->NextRecord()) $R[]=$r;
		foreach ($R as $r) {
			$T=get_tarif_current($service,$r['id']);
			$D=get_cpe_history($service,$r['id']);
			$T['deposit_sum']=count($D)?($D[count($D)-1]['deposit_sum']):0;
			$ts=strtotime($r['actual_from']);
			$d=getdate($ts);
			$c=cal_days_in_month(CAL_GREGORIAN, $d['mon'], $d['year']);
			$d['mday']--;	//ГЮ РЕЙСЫХИ ДЕМЭ
			$V=call_user_func("conn_calc_".$service,$r,$T,$c-$d['mday'],$c);
			if (is_array($V)){
		   		if ($V[1]!=0 || $V[0]) {
					$bool=1;
		   			do_make_add_line($bill_no,$V[0],$V[2],$V[1],"${period}-01",$service,$r['id']);
					$sum+=$V[1]*$V[2];	//$V[2] = amount
					if ($T['deposit_sum']) {
			   			do_make_add_line($bill_no,'Залог за оборудование',$V[2],$T['deposit_sum'],"${period}-01",$service,$r['id']);
			   			$sum+=$V[2]*$T['deposit_sum'];
					}
				}
		   		if (isset($V[3]) && $V[3]>0) {
					$bool=1;
			   		$am=round(($c-$d['mday'])/$c,2);
			   		$str='Абонентская плата за '.mdate('месяц Y года',$ts);
			   		if ($service=='usage_ip_ports') {
						$str.=' (тариф K-'.$T['mb_month'].'-'.$T['pay_month'].'-'.$T['pay_mb'].', подключение '.$r['id'].')';
			   		} elseif ($service=='usage_voip') {
			   			$str.=' за телефонию ('.$r['no_of_lines'].' линий)';
			   		}
		   			do_make_add_line($bill_no,$str,$am*$V[2],$V[3],"${period}-01",$service,$r['id']);
					$sum+=$am*$V[3];
		   		}
				$db->Query("update $service set status='working' where id='".$r['id']."'");
			}
		}
    }
	if ($bool) {
		do_make_bill_register($bill_no,$bill_date,$client,$sum,'connection',1,$sum_virtual);
		echo "Счёт $bill_no выставлен";
	} else {
		echo "Нечего выставлять";	
	}
?>
