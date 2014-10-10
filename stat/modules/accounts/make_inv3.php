<?php
	error_reporting(E_ALL);
	require_once("../../include_archaic/lib.php");

//	include "../../conf.php";
	
//require_once(INCLUDE_PATH.'util.php');
	require_once("make_inv.php");
function get_param_protected($name,$default = '') {
	if (isset($_GET[$name])){
		$t=$_GET[$name];
	} else if (isset($_POST[$name])){
		$t=$_POST[$name];
	} else if (isset($_COOKIES[$name])){
		$t=$_COOKIES[$name];	
	} else {
		return $default;
	}
	
	return str_protect($t);
};
function str_protect($str){
	//вроде как, те 2 строчки лишние
	$str=str_replace("\\","\\\\",$str);
	$str=str_replace("\"","\\\"",$str);
	return $str;
};

//echo "<h1>make_inv2</h1><br>";
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
	
	$now=date("Y-m-d");
	$todo=get_param_protected('todo');
	if ($todo==''){
	?>
		<h1>Пересчет счетов фактур</h1>
		<form method="POST" action="?todo=make"> 
		<table>
			<TR>
				<TD>Сумма Платежа рублях</TD>
				<td>Сумма платежа в долларах</td>
				<td>Курс</td>
				<TD>Дата платежа</TD>
				<TD>Номер платежного поручения</TD>
				<TD>Номер счета </TD>
				<TD>Клиент</TD>
			</TR>
			<TR>
				<TD><INPUT maxlength="20" size="10" value="0.00" name="pay_sum" type="text"></TD>
				<TD><INPUT maxlength="20" size="10" value="0.00" name="pay_sum_usd" type="text"></TD>
				<TD><INPUT maxlength="7" size="7" value="00.0000" name="rate" type="text"></TD>
				<TD><INPUT maxlength="12" size="10" value="<?=$now;?>" name="pay_date" type="text"></TD>
				<TD><INPUT maxlength="20" size="10" value="" name="pay_pp" type="text"></TD>
				<TD><INPUT maxlength="20" size="10" value="<?=$bill_no;?>" name="bill_no" type="text" readonly="true"></TD>
				<TD><INPUT maxlength="20" size="10" value="<?=$client;?>" name="client" type="text" readonly="true"></TD>
			</TR>
			<TR>
				<TD colspan="6"><INPUT value="Сформировать" type="submit"></TD>
			</TR>
		</table>
		
		</form>
	<?
	
	}else{
		
		$pay_pp=get_param_protected('pay_pp');
		$pay_sum=get_param_protected('pay_sum');
		$pay_sum_usd=get_param_protected('pay_sum_usd');
		$rate=get_param_protected('rate');
		$pay_date=get_param_protected('pay_date');
		make_invoice($pay_pp,$pay_sum,$pay_sum_usd,$rate,$bill_no,$pay_date,1);
	
	};

?>