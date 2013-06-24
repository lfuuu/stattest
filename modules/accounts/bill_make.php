<html>
<head>
<title>stat.mcn.ru/operator :: счета</title>
<script language="JavaScript" type="text/javascript" src="/js/popup.js"></script>
<style type="text/css">
<!--
BODY {
font-family: Tahoma, Sans-serif, Arial; font-size: 10pt;}
TABLE {
font-family: Tahoma, Sans-serif, Tahoma, Arial; font-size: 10pt;}
TD {
font-family: Tahoma, Sans-serif, Tahoma, Arial; font-size: 10pt;}
-->
</style>
</head>
<?php
    error_reporting(E_ALL);
    set_magic_quotes_runtime(0);
    include  "../../include_archaic/lib.php";
    include  "bill_make_lib.php";
    db_open();
    $client=$_GET['client'];
    if (strlen($client)<=0){
	echo "no client specified";
	exit;
    }
    $action=$_GET['action'];
    if (strlen($action)<=0){
	echo "no action specified";
	exit;
    }
    $what=$_GET['what'];
    if (strcmp($what,'bill')==0){
	if (strcmp($action,'make')==0){
?>
<center><b>выставить счет</b></center>
<form>
<input type=hidden name='action' value='do_make'>
<input type=hidden name='what' value='bill'>
<input type=hidden name='client' value='<?echo $client?>'>
<table>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    client
	</td>
	<td BGCOLOR="#C5D6E3">
	    <?echo $client ?>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    Дата
	</td>
	<td BGCOLOR="#C5D6E3">
	    <input type=text name='date_d' value='1' size=2 maxlength=2>/
	    <input type=text name='date_m' value='<?echo date("m");?>' size=2 maxlength=2>/
	    <input type=text name='date_y' value='<?echo date("Y");?>' size=4 maxlength=4>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    Период по факту
	</td>
	<td BGCOLOR="#C5D6E3">
	    <input type=text name='period_f_m' value='<?echo date("m",mktime(0, 0, 0, date("m")-1, date("d"),  date("Y")));?>' size=2 maxlength=2>/
	    <input type=text name='period_f_y' value='<?echo date("Y",mktime(0, 0, 0, date("m")-1, date("d"),  date("Y")));?>' size=4 maxlength=4>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    Период по предоплате
	</td>
	<td BGCOLOR="#C5D6E3">
	    <input type=text name='period_pre_m' value='<?echo date("m");?>' size=2 maxlength=2>/
	    <input type=text name='period_pre_y' value='<?echo date("Y");?>' size=4 maxlength=4>
	</td>
    </tr>
    <tr>
    	<td>Компенсация за непредоставление услуг, часы</td>
    	<td><INPUT type="text" name="comp" value="0" size="3" maxlength="3"></td>
    </tr>
    <tr>
    	<td colspan=2><input type=checkbox name=must_pay value=1 checked>Обязателен к оплате</td>
	</tr>    	
</table>
<br>
<center>
<input type=submit value='Выставить'>
</center>
</form>
<?
    }elseif (strcmp($action,'do_make')==0){
print "...";
	$date=sprintf("%04d-%02d-%02d",$_GET['date_y'],$_GET['date_m'],$_GET['date_d']);
	
	// вставил значек "-" для исправления ошибки перехода на 4 версию mysql 
	$period_f=sprintf("%04d-%02d",$_GET['period_f_y'],$_GET['period_f_m']);
	$period_pre=sprintf("%04d-%02d",$_GET['period_pre_y'],$_GET['period_pre_m']);
	// конец блока правок 
	$comp=(int)$_GET['comp'];
	if (!isset($_GET['$must_pay'])) $must_pay=0; else $must_pay=(int)$_GET['must_pay'];
	$bill_no=do_make_bill($client,$date,$period_f,$period_pre,$comp,'default',$must_pay);
?>
Cчет номер <?echo $bill_no ?> выставлен
<br>
<a href='javascript:opener.location.reload(); self.close()';>закрыть окно</a>
<?
	}
    }

?>
