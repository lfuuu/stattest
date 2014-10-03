<html>
<head>
<title>stat.mcn.ru/operator :: услуги</title>
<script language="JavaScript" type="text/javascript" src="/js/popup.js"></script>
<style type="text/css">
<!--
BODY {
font-family: Tahoma, Sans-serif, Arial; font-size: 10pt;}
TABLE {
font-family: Tahoma, Sans-serif, Tahoma, Arial; font-size: 10pt;}
TD {
font-family: Tahoma, Sans-serif, Tahoma, Arial; font-size: 10pt; background-color: #C5D6E3 }
-->
</style>
<SCRIPT type="text/javascript">
  function calculate(i){
      var  d=5;

      // for (i=0;i<num_lines;i++){
        document.forms[0].elements[i*d+5].value=document.forms[0].elements[i*d+3].value*document.forms[0].elements[i*d+4].value;
  //  };
  }
</SCRIPT>
</head>
<body onunload="javascript: opener.location.reload(); ">
<?php
    set_magic_quotes_runtime(0);
    require "../../include_archaic/lib.php";

function make_balance_correction_nodb($client,$sum){
	if ($sum>=0) $sum='+'.$sum;
	$q=mysql_query("select * from balance where client='{$client}'");
	if (!($r=mysql_fetch_row($q))) mysql_query("insert into balance (client) values ('{$client}');");
	mysql_query("update balance set sum=sum{$sum} WHERE client='{$client}'");
}

    db_open();
    $error_flag=false;
     $bill_no=$_GET['bill'];
     if (!isset($bill_no)) {
         echo "Неизвестный номер счета";
         exit;
     };
    $action=(isset($_GET['action'])?$_GET['action']:'');
    if ($action==='addempty') {
    	$q=mysql_query("select count(*) from bill_bill_lines where (item_date='0000-00-00') and (bill_no='$bill_no')");
    	$c=mysql_fetch_row($q);
    	$q=mysql_query("select max(line) FROM bill_bill_lines where (bill_no='$bill_no')");
   		$r=mysql_fetch_row($q);
   		$r[0]++;
    	if ($c[0]==3) {
	    	$q=mysql_query("update bill_bill_lines set line=line+1 where (item_date='0000-00-00') and (bill_no='$bill_no') order by line desc");
			$r[0]-=3;
    	}
    	mysql_query("insert into bill_bill_lines (bill_no,line,item_date) VALUES ('{$bill_no}',{$r[0]},NOW())");
    } elseif ($action==='update'){
        $lines=$_POST['line'];
        $amount=$_POST['amount'];
        $price=$_POST['price'];
        $item=$_POST['item'];
        $sum=$_POST['sum'];
        $must_pay=(isset($_POST['must_pay'])?$_POST['must_pay']:0);
        $sum_total=0;
        for($i=0;$i<count($lines);$i++)
        {

            $query="UPDATE bill_bill_lines
             SET item='{$item[$i]}',
                 amount={$amount[$i]},
                 price={$price[$i]},
                 sum={$sum[$i]}
                 WHERE bill_no='$bill_no'
                       AND line={$lines[$i]}";
            // echo "amount[$i]=$amount[$i]<br>";
             if(0==$amount[$i]){
                 $query= "DELETE FROM bill_bill_lines
                          WHERE bill_no='$bill_no'
                                AND line={$lines[$i]}";
             };
      //   echo $query."<br>";
            $res=mysql_query($query);
            if(!$res) {
	            $error_flag=true;
	            echo $error_flag."<br>";
	         };
            $sum_total+=$sum[$i];
         }
         $nds=$sum_total*0.18;
         $total=$sum_total+$nds;
         $query1="UPDATE bill_bill_lines
                    SET sum=$sum_total
                    WHERE bill_no='$bill_no'
                        AND item='*Итого :'";
         $query2="UPDATE bill_bill_lines
                    SET sum=$nds
                    WHERE bill_no='$bill_no'
                        AND item='*НДС 18% :'";
         $query3="UPDATE bill_bill_lines
                    SET sum=$total
                    WHERE bill_no='$bill_no'
                        AND item='*Всего с НДС :'";
         
         $q=mysql_query("select sum,client from bill_bills WHERE bill_no='$bill_no'");
         $r=mysql_fetch_array($q);
         make_balance_correction_nodb($r['client'],-($total-$r['sum']));
         $query4="UPDATE bill_bills
                    SET sum=$total, must_pay=$must_pay
                    WHERE bill_no='$bill_no'";

       
         $res1=mysql_query($query1) or  mysql_error();
         $res2=mysql_query($query2) or  mysql_error();
         $res3=mysql_query($query3) or  mysql_error();
         $res4=mysql_query($query4) or  mysql_error();
        // echo $error_flag."<br>";
         if (!$res1 or !$res2 or !$res3 or !$res4) $error_flag=true;
         if (!$error_flag) {echo"<h1>Изменения внесены. Вы можете или закрыть окно или внести новые изменения в счет</h1><br><p> Для удаления позиции из счета укажите количестко равное 0</p>";
               // echo "<br>$query1  - $res1 ;<br> $query2  - $res2 ;<br> $query3  - $res3 ;<br> $query4  - $res4  <br>";
         };
     };
    if ($error_flag) {
	    echo"<h1> Некоторые строки в счете не были обновлены</h1>";
       echo "<br>$query1  - $res1 ;<br> $query2  - $res2 ;<br> $query3  - $res3 ;<br> $query4  - $res4  <br>";
	 };
        
    
    // Выводим форму для редактирования счета 
    $req="select bill_no,client,company_full,address_post,fax,DATE_FORMAT(bill_date,'%d.%m.%Y') as bill_date_f,sum,usd_rate_percent,state,must_pay from bill_bills ".
        "where bill_no='$bill_no'";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    if(!($row = mysql_fetch_array($result)))
        {echo "no date database for bill $bill_no!<br>"; exit;}
    $sum=$row['sum'];
    $row['sum_in_words']=spell_number($sum,'USD');
    $row['usd_rate_percent']+=0;
    $company_full=$row[2];
	$must_pay=$row['must_pay'];

    $req="select bill_no,line,item,amount,price,sum from bill_bill_lines ".
        "where bill_no='$bill_no' order by line asc";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    $lines="";
    ?>
    <h1>Изменение Счета</h1>

    <FORM  name="f" action="bill_edit.php?action=update&bill=<?=$bill_no;?>" method="POST">
    <table border=1>
    <tr>
        <td> Счет номер:</td><td colspan=4><b><?=$bill_no;?></b></td>
    </tr>
    <tr>
    <td>Клиент:</td><td colspan=4><b><?=$company_full;?></b></td>
    </tr>
    <tr>
    <td><input type=checkbox value=1 name=must_pay<?if($must_pay)echo " checked";?>></td><td colspan=4>Обязателен к оплате</td>
    </tr>
    <tr>
        <td>п/п</td>
        <td>Предмет счета</td>
        <td>Количество</td>
        <td>Стоимость</td>
        <td>Сумма</td>

    </tr>
    <?
    $num_lines=mysql_num_rows($result);
    $num_of_line=0;
    while($row = mysql_fetch_array($result)){
	if (strcmp(substr($row['item'],0,1),'*')!==0){

	    ?>
        <tr>
        <td>
            <INPUT TYPE='HIDDEN'  name='line[<?=$num_of_line; ?>]' value='<?=$row["line"]?>'>
           <?=  $row["line"]?>
        </td>
         <td>

            <INPUT TYPE='text' size="100" name='item[<?=$num_of_line; ?>]' value='<?=$row["item"]?>'>
        </td>
        <td>
            <INPUT TYPE='text' onchange="calculate(<?=$num_of_line;?>)" size="10" name='amount[<?=$num_of_line; ?>]' value='<?=$row["amount"]?>'>
        </td>
        <td>
            <INPUT TYPE='text' onchange="calculate(<?=$num_of_line;?>)" size="10" name='price[<?=$num_of_line; ?>]' value='<?=$row["price"]?>'>
        </td>
        <td>
           <INPUT TYPE='TEXT' size=8 name='sum[<?=$num_of_line; ?>]' value="<?=$row["sum"]?>" readonly>
        </td>
        </tr>
        <?
        $num_of_line++;
        }

	}
    ?>
    <tr>
    <td colspan=5 aligne="right">
      <INPUT TYPE="button" name="submit1" value="Внести изменения" onClick="this.form.submit();">
      <a href='?action=addempty&bill=<?=$bill_no;?>'>Добавить пустую строчку</a>
    </td>
    </tr>
    </table>
	</form>
    </body>



