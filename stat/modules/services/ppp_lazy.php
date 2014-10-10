<html>
<head>
<title>stat.mcn.ru/operator :: добавление PPPOE логина</title>
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
    set_magic_quotes_runtime(0);
    require "../../include_archaic/lib.php";
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
    if (strcmp($action,'add')==0){
    $req="select login,ip,nat_to_ip from usage_ip_ppp ".
        "where client='".$client."' order by id";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    $affix=0;
    $ip='';
    $nat2ip='';
    
    $affix=mysql_num_rows($result);
    while($row = mysql_fetch_array($result))
    {
	//$affix[]=substr($row[0],strlen($client));
	$ip=$row['ip'];
	$nat2ip=$row['nat_to_ip'];
    }
    
   
    mysql_free_result($result);
    $affix++;
    $login=$client.$affix;
    if(strcmp($ip,"")==0)
    {
	echo "У клиента $client нет ни одного PPPOE логина.<br>";
	echo "Обратитесь к администратору для заведения нового логина.<br>";
	exit;
    }
    $sip=explode(".",$ip);
    if($sip[3]>=254)
    {
	echo "Невозможно выделить следующий IP адрес для клиента $client.<br>";
	echo "Обратитесь к администратору для заведения нового логина.<br>";
	exit;
    }
    $sip[3]++;
    $ip=implode(".",$sip);
?>
<center><b>новое подключение PPPOE</b></center>
<form method=GET action=ppp_lazy.php>
<input type=hidden name='action' value='do_add'>
<input type=hidden name='client' value='<? echo $client ?>'>
<input type=hidden name='login' value='<? echo $login ?>'>
<input type=hidden name='ip' value='<? echo $ip ?>'>
<input type=hidden name='nat_to_ip' value='<? echo $nat2ip ?>'>
<table>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    client
	</td>
	<td BGCOLOR="#C5D6E3">
	    <?php echo $client ?>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    PPPOE login
	</td>
	<td BGCOLOR="#C5D6E3">
	    <?php echo $login ?>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    IP-адрес
	</td>
	<td BGCOLOR="#C5D6E3">
	    <?php echo $ip ?>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    nat_to_ip
	</td>
	<td BGCOLOR="#C5D6E3">
	    <?php echo $nat2ip ?>
	</td>
    </tr>
</table>
<br>
<center>
<input type=submit value='Добавить'>
</center>
</form>
<?
    }elseif (strcmp($action,'do_add')==0){
	$client=$_GET['client'];
	$login =$_GET['login'];
	$ip    =$_GET['ip'];
	$nat_to_ip=$_GET['nat_to_ip'];
	srand(time());
	$password=substr(md5($client.$login.microtime().rand()),0,8);
	$req="insert into usage_ip_ppp ".
	    "(login,password,client,ip,nat_to_ip,actual_from,actual_to) ".
	    "values ".
	    "('$login','$password','$client','$ip','$nat_to_ip',NOW(),NOW())";
	if (!($result = mysql_query($req,$GLOBALS['dbh'])))
    	    {echo "can't write to database!<br>$req"; exit;}
?>
Новое PPPOE подключение зарегистрировано.<br>
Клиент: <B><? echo $client ?></B><BR>
Логин: <B><? echo $login ?></B><BR>
Пароль: <B><? echo $password ?></B><BR>
<a href='javascript:self.close()';>закрыть окно</a>
<?
    }
?>
