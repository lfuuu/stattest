<?php
error_reporting(E_ALL);
// номер платежки
//
function make_invoice($pay_no, $pay_sum_rub, $pay_sum_usd,$pay_rate,$bill,$pay_date, $fb=0){
//echo "начинаем делать счета фактур<br>";	
if (!($pay_sum_rub>0 and $pay_sum_usd >0)){
	echo "Неверные данные";
	return false;
	
}


$pay_rate=$pay_sum_rub/$pay_sum_usd;

//echo "<h1>Function make_invoice</h1>";
/*printdbg($pay_no,"платежка номер");
printdbg($pay_sum_rub,"pay_sum_rub");
printdbg($pay_sum_usd,"pay_sum_usd");
printdbg($pay_rate,"pay_rate");
printdbg($bill,"bill");
printdbg($pay_date,"pay_date");
*/

$tax=18; /* НДС NDS 18% */
set_magic_quotes_runtime(0);
//require_once("include/lib.php");
//echo "перед открытием базы<br>";
db_open();
//echo "после  открытия базы<br>";

//echo "bill='$bill'";

$req="select bill_no,bill_date,DATE_FORMAT(bill_date,'%d.%m.%Y') as date_f,client,company_full,address_post,fax,sum,usd_rate_percent,state from bill_bills "."where bill_no='$bill'";
if (!($result = mysql_query($req,$GLOBALS['dbh'])))
	{echo "can't read from database!<br>$req"; exit;}
//echo "num_rows=".mysql_num_rows($result)."<br>";
if(!($bill_row = mysql_fetch_array($result)))
	{echo "no data in database for bill $bill!<br>$req<br>"; exit;}
$client=$bill_row['client'];
$req="select company_full,address_post,address_jur,phone,inn,kpp,usd_rate_percent from clients ".
	"where client='".$client."'";
if (!($result = mysql_query($req,$GLOBALS['dbh'])))
	{echo "can't read from database!<br>$req"; exit;}
if(!($client_row = mysql_fetch_array($result)))
	{echo "no data in database for client $client!<br>"; exit;}
echo 'Счет N '.$bill_row['bill_no'].' от '.$bill_row['date_f'].' на сумму $'.$bill_row['sum']."\n<br>\n";
echo 'Плательщик:'.$bill_row['company_full'];
	
	/**** register invoice */
	/* clear old invoices on this bill */
	$req="delete from bill_invoices where bill_no='${bill_row['bill_no']}' or invoice_no like '${bill_row['bill_no']}%'";
	if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
	$req="delete from bill_invoice_lines where invoice_no like '${bill_row['bill_no']}%'";
	if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
	/* adjust currency rate to fit invoice sum into payment sum */
	$usd_rate_ajusted=$pay_sum_rub/$pay_sum_usd;
//	    print "$pay_sum_usd $pay_sum_rub | rate_ajusted=$usd_rate_ajusted";
	/* calculated preciese tax from bill, including rounding-error factor*/
	$req="select sum(sum) as sum from bill_bill_lines where bill_no='${bill_row['bill_no']}' and item not like '*%'";
	if (!($tmp1_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}
	$tmp1_row = mysql_fetch_array($tmp1_result);
	$bill_sum = $tmp1_row['sum'];
	$req="select sum from bill_bill_lines where bill_no='${bill_row['bill_no']}' and item='*Всего с НДС :'";
	if (!($tmp1_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}
	$tmp1_row = mysql_fetch_array($tmp1_result);
	$bill_sum_plus_tax = $tmp1_row['sum'];
	$tax_with_bill_rounding_error=$bill_sum_plus_tax/$bill_sum;	    
//	    print "$bill_sum | $bill_sum_plus_tax | $tax_with_bill_rounding_error";
	/* re-calc and register invoice_lines */
	$inv_no_suffix=1; /* invoice number */
	$req="select item_date from bill_bill_lines  where bill_no='${bill_row['bill_no']}' and item not like '*%' group by item_date";
	if (!($bill_line_group_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}
	// формируем обычние счета но без залога
	while(($bill_line_group_row = mysql_fetch_array($bill_line_group_result))){
		$inv_item_date=$bill_line_group_row['item_date'];
		$inv_no=$bill_row['bill_no'].'-'.$inv_no_suffix;
		$inv_sum=0;
		$inv_line=1;
		$sum_total=0;
		$sum_usd_total=0;
		$tax_sum_total=0;
		$tax_sum_usd_total=0;
		$sum_plus_tax_total=0;
		$sum_plus_tax_usd_total=0;
		$req="select * from bill_bill_lines  where bill_no='${bill_row['bill_no']}' and item not like '*%' and item_date='$inv_item_date'";
		if (!($bill_line_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}
		while(($bill_line_row = mysql_fetch_array($bill_line_result))){
		$item=$bill_line_row['item'];
		$ediz='шт.';
		$amount=$bill_line_row['amount'];
		/* usd */
		$price_usd=$bill_line_row['price'];
		if ($price_usd<0){$corr_sign=-1;}else{$corr_sign=+1;}
		$sum_usd=$bill_line_row['sum'];
		$tax_sum_usd=$bill_line_row['sum']*($tax_with_bill_rounding_error-1);
		$sum_plus_tax_usd=$bill_line_row['sum']*$tax_with_bill_rounding_error;
		/* usd - totals */
		$sum_plus_tax_usd_total+=$sum_plus_tax_usd;

		/* rub */
		$sum_plus_tax=round(($sum_plus_tax_usd*$usd_rate_ajusted),2);

		/* rub - totals */
		$sum_plus_tax_total+=$sum_plus_tax;
		/* ajust item name for the fucked compensation */
		if (strstr($item, "Компенсация за непредоставление услуг") !== FALSE ) {
			$item='Компенсация за непредоставление услуг, часы';
		}

		/* do the ,2 5/4 rounding to sum+tax only before db write*/
		$sum_plus_tax_usd=(int)($sum_plus_tax_usd*100+$corr_sign*0.5)/100;
		//$sum_plus_tax=(int)($sum_plus_tax*100+$corr_sign*0.5)/100;
		/* recount sum and tax_sum based on rounded value*/
		$sum=round(($sum_plus_tax/(1+($tax/100))),2);
		$tax_sum=$sum_plus_tax-$sum;
		$price=round(($sum/$amount),2);

		$req="insert into bill_invoice_lines (".
			"invoice_no,line,item,ediz,amount,".
			"price,sum,tax,tax_sum,sum_plus_tax,".
			"price_usd,sum_usd,tax_sum_usd,sum_plus_tax_usd".
			") values (".
			"'$inv_no','$inv_line','$item','$ediz','$amount',".
			"'$price','$sum','$tax','$tax_sum','$sum_plus_tax',".
			"'$price_usd','$sum_usd','$tax_sum_usd','$sum_plus_tax_usd')";
	// тестовый блок
	if (strstr($item, 'Возврат задатка')!== FALSE)
	{
		//  echo "<br> price= ".$price."<br>";
		// echo"<br> $req" ;
	};
	//конец тестового блока
	if(strstr($item,"Залог")===false){
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
		$inv_line++;
	}else {
		$sum_plus_tax_usd_total-=$sum_plus_tax_usd;
		$sum_plus_tax_total-=$sum_plus_tax;
	}
		}
	//	mysql_close($bill_line_result);
		$sum_plus_tax_total=round($sum_plus_tax_total,2);
		$sum_total=$sum_plus_tax_total/(1+($tax/100));
		$tax_sum_total=$sum_plus_tax_total-$sum_total;

		$sum_plus_tax_usd_total=(int)($sum_plus_tax_usd_total*100+0.5)/100;
		$sum_usd_total=$sum_plus_tax_usd_total/(1+($tax/100));
		$tax_sum_usd_total=$sum_plus_tax_usd_total-$sum_usd_total;

		/* do the ,2 5/4 rounding to sum+tax only before db write*/
		//$sum_plus_tax_usd_total=(int)($sum_plus_tax_usd_total*100+$corr_sign*0.5)/100;
		//$sum_plus_tax_total=(int)($sum_plus_tax_total*100+$corr_sign*0.5)/100;
		{
		/* add 'totals' line */
		$req="insert into bill_invoice_lines (".
			"invoice_no,line,item,".
			"sum,tax_sum,sum_plus_tax,".
			"sum_usd,tax_sum_usd,sum_plus_tax_usd".
			") values (".
			"'$inv_no','$inv_line','*Всего к оплате',".
			"'$sum_total','$tax_sum_total','$sum_plus_tax_total',".
			"'$sum_usd_total','$tax_sum_usd_total','$sum_plus_tax_usd_total')";
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
		}
		$inv_no_suffix++;
		$inv_line++;
		/* register invoice */
		$date=substr($inv_item_date,0,8).strftime("%d", mktime(0, 0, 0, substr($inv_item_date,5,2)+1, 0, substr($inv_item_date,0,4))); /* last day of month in which service was provided */
		$req="insert into bill_invoices (".
		"invoice_no,invoice_date,bill_no,bill_date,pay_date,pay_no,client,company_full,address_jur,address_post,inn,kpp,".
		"sum,tax_sum,sum_plus_tax,".
		"sum_usd,tax_sum_usd,sum_plus_tax_usd".
		") values (".
		"'$inv_no','$date','".$bill_row['bill_no']."','".$bill_row['date_f']."','$pay_date','$pay_no','$client','".$client_row['company_full']."','".$client_row['address_jur']."','".$client_row['address_post']."','".$client_row['inn']."','".$client_row['kpp']."',".
		"'$sum_total','$tax_sum_total','$sum_plus_tax_total',".
		"'$sum_usd_total','$tax_sum_usd_total','$sum_plus_tax_usd_total')";
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
	}// конец формирование  счетов фактур без залога
	///////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	////////////////////////ZALOG  ZALOG//////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	
//	echo "<h1>Делаем залог</h1>"  ;
//GLOBAL  $inv_no_suffix ,$bill_row, $tax_with_bill_rounding_error, $usd_rate_ajusted, $tax, $pay_date,$pay_no, $client, $client_row ;
// echo  $inv_no_suffix."<br>" ;
$req="select item_date from bill_bill_lines  where bill_no='${bill_row['bill_no']}' and instr(item, 'Залог') group by item_date";

	if (!($bill_line_group_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}

//   echo $bill_line_group_result."<br> выполнили запрос $req<br>".mysql_num_rows($bill_line_group_result);
	// формируем обычние счета для  залога
	while(($bill_line_group_row = mysql_fetch_array($bill_line_group_result))){
		$inv_item_date=$bill_line_group_row['item_date'];
		$inv_no=$bill_row['bill_no'].'-'.$inv_no_suffix;
		$inv_sum=0;
		$inv_line=1;
		$sum_total=0;
		$sum_usd_total=0;
		$tax_sum_total=0;
		$tax_sum_usd_total=0;
		$sum_plus_tax_total=0;
		$sum_plus_tax_usd_total=0;
		$req="select * from bill_bill_lines  where bill_no='${bill_row['bill_no']}' and instr(item, 'Залог') and item_date='$inv_item_date'";
// echo $req."<br>";
		if (!($bill_line_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}
		while(($bill_line_row = mysql_fetch_array($bill_line_result))){
		$item=$bill_line_row['item'];
		$ediz='шт.';
		$amount=$bill_line_row['amount'];
		/* usd */
		$price_usd=$bill_line_row['price'];
		if ($price_usd<0){$corr_sign=-1;}else{$corr_sign=+1;}
		$sum_usd=$bill_line_row['sum'];
		$tax_sum_usd=$bill_line_row['sum']*($tax_with_bill_rounding_error-1);
		$sum_plus_tax_usd=$bill_line_row['sum']*$tax_with_bill_rounding_error;
		/* usd - totals */
		$sum_plus_tax_usd_total+=$sum_plus_tax_usd;

		/* rub */
		$sum_plus_tax=round(($sum_plus_tax_usd*$usd_rate_ajusted),2);

		/* rub - totals */
		$sum_plus_tax_total+=$sum_plus_tax;
		/* ajust item name for the fucked compensation */
		if (strstr($item, "Компенсация за непредоставление услуг") !== FALSE ) {
			$item='Компенсация за непредоставление услуг, часы';
		}

		/* do the ,2 5/4 rounding to sum+tax only before db write*/
		$sum_plus_tax_usd=(int)($sum_plus_tax_usd*100+$corr_sign*0.5)/100;
		$sum_plus_tax=(int)($sum_plus_tax*100+$corr_sign*0.5)/100;
		/* recount sum and tax_sum based on rounded value*/
		$sum=$sum_plus_tax/(1+($tax/100));
		$tax_sum=$sum_plus_tax-$sum;
		$price=$sum/$amount;

		$req="insert into bill_invoice_lines (".
			"invoice_no,line,item,ediz,amount,".
			"price,sum,tax,tax_sum,sum_plus_tax,".
			"price_usd,sum_usd,tax_sum_usd,sum_plus_tax_usd".
			") values (".
			"'$inv_no','$inv_line','$item','$ediz','$amount',".
			"'$price','$sum','$tax','$tax_sum','$sum_plus_tax',".
			"'$price_usd','$sum_usd','$tax_sum_usd','$sum_plus_tax_usd')";
	//   echo "$req<br>" ;
	// тестовый блок
	if (strstr($item, 'Возврат задатка')!== FALSE)
	{
		//  echo "<br> price= ".$price."<br>";
		// echo"<br> $req" ;
	};
	//конец тестового блока

		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
		$inv_line++;

		}
		//mysql_close($bill_line_result);
		$sum_plus_tax_total=(int)($sum_plus_tax_total*100+0.5)/100;
		$sum_total=$sum_plus_tax_total/(1+($tax/100));
		$tax_sum_total=$sum_plus_tax_total-$sum_total;

		$sum_plus_tax_usd_total=(int)($sum_plus_tax_usd_total*100+0.5)/100;
		$sum_usd_total=$sum_plus_tax_usd_total/(1+($tax/100));
		$tax_sum_usd_total=$sum_plus_tax_usd_total-$sum_usd_total;

		/* do the ,2 5/4 rounding to sum+tax only before db write*/
		$sum_plus_tax_usd_total=(int)($sum_plus_tax_usd_total*100+$corr_sign*0.5)/100;
		$sum_plus_tax_total=(int)($sum_plus_tax_total*100+$corr_sign*0.5)/100;
		{
		/* add 'totals' line */
		$req="insert into bill_invoice_lines (".
			"invoice_no,line,item,".
			"sum,tax_sum,sum_plus_tax,".
			"sum_usd,tax_sum_usd,sum_plus_tax_usd".
			") values (".
			"'$inv_no','$inv_line','*Всего к оплате',".
			"'$sum_total','$tax_sum_total','$sum_plus_tax_total',".
			"'$sum_usd_total','$tax_sum_usd_total','$sum_plus_tax_usd_total')";
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
		}
		$inv_no_suffix++;
		$inv_line++;
		/* register invoice */
		$date=substr($inv_item_date,0,8).strftime("%d", mktime(0, 0, 0, substr($inv_item_date,5,2)+1, 0, substr($inv_item_date,0,4))); /* last day of month in which service was provided */
		$req="insert into bill_invoices (".
		"invoice_no,invoice_date,bill_no,bill_date,pay_date,pay_no,client,company_full,address_jur,address_post,inn,kpp,".
		"sum,tax_sum,sum_plus_tax,".
		"sum_usd,tax_sum_usd,sum_plus_tax_usd".
		") values (".
		"'$inv_no','$date','".$bill_row['bill_no']."','".$bill_row['date_f']."','$pay_date','$pay_no','$client','".$client_row['company_full']."','".$client_row['address_jur']."','".$client_row['address_post']."','".$client_row['inn']."','".$client_row['kpp']."',".
		"'$sum_total','$tax_sum_total','$sum_plus_tax_total',".
		"'$sum_usd_total','$tax_sum_usd_total','$sum_plus_tax_usd_total')";

	// echo "<br><br>$req" ;
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
	// конец формирование  счетов фактур  залога

	
	
	
	
//	echo "Закончили залоги";
	
	////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////
	/////////////////////   KONEC ZALOGA /////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////
	
	
}	
	
	//do_inv_zalog();
	//mysql_close($bill_line_group_result);
	if ($fb==0){
		$req="update bill_bills set state='payed' where bill_no='$bill'";
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
		echo "Счет проведен как оплаченный<br>";
	}
	echo "<a href='javascript:self.close()';>закрыть окно</a>";
	


}




function mysql_get1($col_name,$table_name,$where_expression){
$return_result='';
$my_req="select $col_name from $table_name $where_expression";
// echo $my_req;
if (!($my_result = mysql_query($my_req,$GLOBALS['dbh'])))
	{echo "can't read from database!<br>$my_req"; exit;}
if($my_row = mysql_fetch_array($my_result)){
	$return_result=$my_row[$col_name];
}
return $return_result;
}

function do_inv_zalog(){
  echo "<h1>Делаем залог</h1>"  ;
GLOBAL  $inv_no_suffix ,$bill_row, $tax_with_bill_rounding_error, $usd_rate_ajusted, $tax, $pay_date,$pay_no, $client, $client_row ;
 echo  $inv_no_suffix."<br>" ;
$req="select item_date from bill_bill_lines  where bill_no='${bill_row['bill_no']}' and instr(item, 'Залог') group by item_date";

	if (!($bill_line_group_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}
//   echo $bill_line_group_result."<br>";
	// формируем обычние счета для  залога
	while(($bill_line_group_row = mysql_fetch_array($bill_line_group_result))){
		$inv_item_date=$bill_line_group_row['item_date'];
		$inv_no=$bill_row['bill_no'].'-'.$inv_no_suffix;
		$inv_sum=0;
		$inv_line=1;
		$sum_total=0;
		$sum_usd_total=0;
		$tax_sum_total=0;
		$tax_sum_usd_total=0;
		$sum_plus_tax_total=0;
		$sum_plus_tax_usd_total=0;
		$req="select * from bill_bill_lines  where bill_no='${bill_row['bill_no']}' and instr(item, 'Залог') and item_date='$inv_item_date'";
// echo $req."<br>";
		if (!($bill_line_result = mysql_query($req,$GLOBALS['dbh']))) {echo "can't read from database!<br>$req"; exit;}
		while(($bill_line_row = mysql_fetch_array($bill_line_result))){
		$item=$bill_line_row['item'];
		$ediz='шт.';
		$amount=$bill_line_row['amount'];
		/* usd */
		$price_usd=$bill_line_row['price'];
		if ($price_usd<0){$corr_sign=-1;}else{$corr_sign=+1;}
		$sum_usd=$bill_line_row['sum'];
		$tax_sum_usd=$bill_line_row['sum']*($tax_with_bill_rounding_error-1);
		$sum_plus_tax_usd=$bill_line_row['sum']*$tax_with_bill_rounding_error;
		/* usd - totals */
		$sum_plus_tax_usd_total+=$sum_plus_tax_usd;

		/* rub */
		$sum_plus_tax=$sum_plus_tax_usd*$usd_rate_ajusted;

		/* rub - totals */
		$sum_plus_tax_total+=$sum_plus_tax;
		/* ajust item name for the fucked compensation */
		if (strstr($item, "Компенсация за непредоставление услуг") !== FALSE ) {
			$item='Компенсация за непредоставление услуг, часы';
		}

		/* do the ,2 5/4 rounding to sum+tax only before db write*/
		$sum_plus_tax_usd=(int)($sum_plus_tax_usd*100+$corr_sign*0.5)/100;
		$sum_plus_tax=(int)($sum_plus_tax*100+$corr_sign*0.5)/100;
		/* recount sum and tax_sum based on rounded value*/
		$sum=$sum_plus_tax/(1+($tax/100));
		$tax_sum=$sum_plus_tax-$sum;
		$price=$sum/$amount;

		$req="insert into bill_invoice_lines (".
			"invoice_no,line,item,ediz,amount,".
			"price,sum,tax,tax_sum,sum_plus_tax,".
			"price_usd,sum_usd,tax_sum_usd,sum_plus_tax_usd".
			") values (".
			"'$inv_no','$inv_line','$item','$ediz','$amount',".
			"'$price','$sum','$tax','$tax_sum','$sum_plus_tax',".
			"'$price_usd','$sum_usd','$tax_sum_usd','$sum_plus_tax_usd')";
	//   echo "$req<br>" ;
	// тестовый блок
	if (strstr($item, 'Возврат задатка')!== FALSE){
		//  echo "<br> price= ".$price."<br>";
		// echo"<br> $req" ;
	};
	//конец тестового блока

		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
		$inv_line++;

		}
		//mysql_close($bill_line_result);
		$sum_plus_tax_total=(int)($sum_plus_tax_total*100+0.5)/100;
		$sum_total=$sum_plus_tax_total/(1+($tax/100));
		$tax_sum_total=$sum_plus_tax_total-$sum_total;

		$sum_plus_tax_usd_total=(int)($sum_plus_tax_usd_total*100+0.5)/100;
		$sum_usd_total=$sum_plus_tax_usd_total/(1+($tax/100));
		$tax_sum_usd_total=$sum_plus_tax_usd_total-$sum_usd_total;

		/* do the ,2 5/4 rounding to sum+tax only before db write*/
		$sum_plus_tax_usd_total=(int)($sum_plus_tax_usd_total*100+$corr_sign*0.5)/100;
		$sum_plus_tax_total=(int)($sum_plus_tax_total*100+$corr_sign*0.5)/100;
		
		/* add 'totals' line */
		$req="insert into bill_invoice_lines (".
			"invoice_no,line,item,".
			"sum,tax_sum,sum_plus_tax,".
			"sum_usd,tax_sum_usd,sum_plus_tax_usd".
			") values (".
			"'$inv_no','$inv_line','*Всего к оплате',".
			"'$sum_total','$tax_sum_total','$sum_plus_tax_total',".
			"'$sum_usd_total','$tax_sum_usd_total','$sum_plus_tax_usd_total')";
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
		
		$inv_no_suffix++;
		$inv_line++;
		/* register invoice */
		$date=substr($inv_item_date,0,8).strftime("%d", mktime(0, 0, 0, substr($inv_item_date,5,2)+1, 0, substr($inv_item_date,0,4))); /* last day of month in which service was provided */
		$req="insert into bill_invoices (".
		"invoice_no,invoice_date,bill_no,bill_date,pay_date,pay_no,client,company_full,address_jur,address_post,inn,kpp,".
		"sum,tax_sum,sum_plus_tax,".
		"sum_usd,tax_sum_usd,sum_plus_tax_usd".
		") values (".
		"'$inv_no','$date','".$bill_row['bill_no']."','".$bill_row['date_f']."','$pay_date','$pay_no','$client','".$client_row['company_full']."','".$client_row['address_jur']."','".$client_row['address_post']."','".$client_row['inn']."','".$client_row['kpp']."',".
		"'$sum_total','$tax_sum_total','$sum_plus_tax_total',".
		"'$sum_usd_total','$tax_sum_usd_total','$sum_plus_tax_usd_total')";

	// echo "<br><br>$req" ;
		if (!($result = mysql_query($req,$GLOBALS['dbh'])))	{echo "can't write to database!<br>$req"; exit;}
	// конец формирование  счетов фактур  залога

};
};

?>