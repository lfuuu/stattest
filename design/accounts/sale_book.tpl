<h1>Книга продаж за период <b>{$period}</b> Компания: <b>{$firma}</b></h1>


<h2>Всего платежей на сумму: <font color="Red" ><b>{$total}</b></font></h2>
<br><br>
<table border="1">
	<Tr bgcolor="#cfd8df">
		<b>
		<td></td>
		<TD>Клиент</TD>
		<td>Название компани</td>
		<td>ИНН</td>
		<td>КПП</td>
		<td>Платеж</td>
		<td>Дата платежа</td>
		<td>Сумма платежа</td>
		<td>Счета фактур</td>
		</b>
		
		
	</Tr>
	{foreach from=$payments item=pay key=key}
		<tr>
		<TD>{$key+1}</TD>
		<TD><a href="?module=clients&id={$pay.client}" target="_blank">{$pay.client}</a></TD>
		<TD>{$pay.company_full}</a></TD>
		<TD>{$pay.inn}</a></TD>
		<TD>{$pay.kpp}</a></TD>
		<TD><a href="?module=accounts&action=accounts_payments&clients_client={$pay.client}" target="_blank">{$pay.payment_no}</a></TD>
		<TD><a href="?module=accounts&action=accounts_payments&clients_client={$pay.client}" target="_blank">{$pay.payment_date}</a></TD>
		<TD><b>{$pay.sum_rub}</b></TD>
		<TD> 
			{foreach from=$pay.invoice item=invoice}
				<a href="modules/accounts/view_inv.php?invoice_no={$invoice.invoice_no}&todo=invoice" target="_blank">
				{$invoice.invoice_no}</a> от {$invoice.invoice_date}<br>
			{/foreach}
		</TD>
		</tr>
			
		
	</tr>
	
	{/foreach}
</table>


