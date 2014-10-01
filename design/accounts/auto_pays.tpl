<html>

<head>
<LINK title=default href="" type=text/css rel=stylesheet>
<title>Автоматический ввод платежей</title>

</head>


<body bgcolor="#FFFFFF" text="#000000">
<h1>Автоматический ввод платежей</h1>

{foreach from=$payments item=pay key=key}
<br><hr><hr><br>
<FORM action="add_auto_pay.php" method="POST" target="_blank">
  <TABLE border="1" bgcolor="#c5d6e3">
    <tr>
    	<TD>Платильщик</TD>
    	<td>{$pay.company}</td>
    	<td>Логин</td>
    	<TD><INPUT type="text" name="client" value="{$pay.client}" size="10"></TD>
    	<TD>ИНН</TD><TD>{$pay.inn}</TD>
    </tr>
    <tr>
    	<TD>Назначение платежа</TD>
    	<TD colspan="3">{$pay.comments}</TD>
    </tr>
  </TABLE>
  <table border="1" bgcolor="#c5d6e3">
  	<TR>
  		<TD>Сумма</TD>
  		<TD>Дата </TD>
  		<TD>Номер</TD>
  		<TD>Счет</TD>
  		
  	</TR>
  	<TR>
  		<TD>{$pay.sum_rub} <INPUT type="hidden" name="sum_rub" value="{$pay.sum_rub}"></TD>
  		<TD>{$pay.payment_date}<INPUT type="hidden" name="pay_date" value="{$pay.payment_date}"></TD>
  		<TD>{$pay.payment_pp}<INPUT type="hidden" name="pay_pp" value="{$pay.payment_pp}"></TD>
  		<TD>
  			<SELECT name="bill_no">
  			<option selected="selected" value="{$pay.bill_no}">{$pay.bill_no}<option>
          			{foreach from=$pay.bills item=bill}
          			{if $bill neq $pay.bill_no}
          				<option value="{$bill}">{$bill}<option>
          			{/if}
          			{/foreach}
        		</SELECT>
  		
  		</TD>
  		
  	</TR>
  	<tr>
  		{if $pay.valid}
  		<TD>Клиент и счет существуют, можно внести платеж</TD>
  		<TD><INPUT type="submit" name="add_auto_pay" value="Внести платеж"></TD>
  		{else}
  		<TD bgcolor="Red"><b>Внимание!!! Клиент или счет не опознан автоматически.
  		Вы можете скорректировать данные в этой форме, но будьте предельно внимательны!!!</b></TD>
  		<TD><INPUT type="submit" name="add_auto_pay" value="Внести платеж"></TD>
  		{/if}
  	</tr>
  </table>
</FORM>

{/foreach}


</body>
</html>