<h2>Платежи</h2>

<table cellpadding="10" cellspacing="0" border="1">
<tr>
	<TD>Платежи всего:<b><FONT color="Red">{$sum_bills}</FONT></b></TD>
	<TD>Оказано услуг на сумму:<b><FONT color="Red">{$sum_bill}</FONT></b></TD>
	<TD>{if $debet > 0}Остаток на счете:{else}Задолженность клиента:{/if}<b><FONT color="Red">{$debet}</FONT></b></TD>
	<TD>Баланс: <b><font color=red>{$balance}</font></b></TD>
</tr>
</table>

<form action="/modules/accounts/add_payment.php" method="POST">
<table align="center">
	<tr>
		<TD>Сумма платежа</TD><td><INPUT type="text" name="pay_sum" value="0000.00"></td>
		<TD>Платежное поручение</TD><td><INPUT type="text" name="pay_pp" value="000"></td>
		<TD>Дата платежа</TD><td><INPUT type="text" name="pay_date" value="ГГГГ-ММ-ДД"></td>
		<TD></TD>
	</tr>

</table>
</form>
<table>
<tr>
<TD>
	<table align="center">
	<tr>
		<td></td>
		<td>Сумма платежа</td>
		<td>Номер платежного поручения</td>
		<td>Дата платежа</td>
	</tr>
	{foreach from=$payments item=payment key=key}
        <tr>
        	<td>{$key}</td>
        	<td>{$pament.sum_rub}</td> 
        	<td>{$payment.payment_no}</a></td> 
        	<td>{$payment.payment_date}</td>
        	
        </tr>


	{/foreach}

	</table>

</TD>

<TD>
	<table align="center">
	<tr>
		<td></td>
		<td>Счет номер</td>
		<td>Сумма</td>
		<td>Дата</td>
		<TD></TD>
	</tr>
	{foreach from=$bills item=bill key=key}
        <tr  {if $bill.sum <table $debet} bgcolor="Purple"{/if} >
        	<td>{$key}</td>
        	<td>{$bill.bill_no}</td> 
        	<td>{$bill.sum}</a></td> 
        	<td>{$bill.bill_date}</td>
        	<td><a href="/modules/accounts/make_invoices.php" target="_blank">Провести счет</a></td>
        	
        </tr>


	{/foreach}

	</table>



</TD>
</tr>
</table>