<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include INCLUDE_ARCHAIC_PATH."lib.php";
//аутентификация
	$module=get_param_raw('module','');
	$action=get_param_raw('action','default');
	$user->DoAction($action);
	$user->DenyInauthorized();
	

	$bill_no=get_param_protected("bill_no");
	if ($bill_no=="") die("Не определен номер счета");
	$client=get_param_protected("client");
	if ($client=="") die("Не определен клиент");
	$bill_date=get_param_protected("date");

	$db->Query('select * from clients where client="'.$client.'" limit 1');
	if (!($r=$db->NextRecord())){
		trigger_error('Такого клиента не существует');
	} else {
		$mail=$r['email'];
		$p=udata_encode($bill_no.','.$client);
		$adr=PROTOCOL_STRING.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/view.php?code='.$p;

		$body="Уважаемые Господа!" . "\n" . "Отправляем Вам счет на оплату услуг:" . "\n";
		$body.=$adr."\n\n Просим своевременно оплатить счет. \n\n";
		echo "<a href='mailto:".$mail."?subject=".rawurlencode ("Счет за интернет")."&body=".rawurlencode ($body)."'>Отправить</a> - utf8<br>";

		$body="сБЮФЮЕЛШЕ цНЯОНДЮ!" . "\n" . "нРОПЮБКЪЕЛ бЮЛ ЯВЕР МЮ НОКЮРС СЯКСЦ:" . "\n";
		$body.=$adr."\n\n оПНЯХЛ ЯБНЕБПЕЛЕММН НОКЮРХРЭ ЯВЕР.";
		echo "<a href='mailto:".$mail."?subject=".rawurlencode ("яВЕР ГЮ ХМРЕПМЕР")."&body=".rawurlencode ($body)."'>нРОПЮБХРЭ</a> - windows<br>";
		$design->ProcessEx('empty.tpl');
	}
?>
