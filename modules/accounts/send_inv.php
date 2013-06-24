<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include "lib.php";
//аутентификация
	$module=get_param_raw('module','');
	$action=get_param_raw('action','default');
	$user->DoAction($action);
	$user->DenyInauthorized();
	

	$invoice_no=get_param_protected("invoice_no");
	if ($invoice_no=="") die("Не определен номер счета-фактуры");

	$query="SELECT * from bill_invoices WHERE invoice_no='$invoice_no'";
	$db->Query($query);
	if (!($row=$db->NextRecord())) exit;
	$client=$row['client'];
	
	$db->Query('select * from clients where client="'.$client.'" limit 1');
	if (!($r=$db->NextRecord())){
		trigger_error('Такого клиента не существует');
	} else {
		$mail=$r['email'];
		$p=udata_encode($invoice_no);
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") {
			$adr='https://';
		} else {
			$adr='http://';
		}
		$adr.=$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/view_inv.php?code='.$p;

		$body="Уважаемые Господа!" . "\n" . "Отправляем Вам счет-фактуру и акт:" . "\n";
		$body.=$adr."&todo=invoice"."\n\n";
		$body.=$adr."&todo=akt"."\n\n";
		echo "<a href='mailto:".$mail."?subject=".rawurlencode ("Счет-фактура и акт")."&body=".rawurlencode ($body)."'>Отправить</a> - koi8<br>";

		$body="сБЮФЮЕЛШЕ цНЯОНДЮ!" . "\n" . "нРОПЮБКЪЕЛ бЮЛ ЯВЕР-ТЮЙРСПС Х ЮЙР:" . "\n";
		$body.=$adr."&todo=invoice"."\n\n";
		$body.=$adr."&todo=akt"."\n\n";
		echo "<a href='mailto:".$mail."?subject=".rawurlencode ("яВЕР-ТЮЙРСПЮ Х ЮЙР")."&body=".rawurlencode ($body)."'>нРОПЮБХРЭ</a> - windows<br>";

		$design->ProcessEx('empty.tpl');
	}


?>
