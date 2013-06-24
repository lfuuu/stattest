<h1>Отчет по услугам за период <b>{$period}</b> Компания: <b>{$firma}</b></h1>


<h2>Всего оказано услуг на сумму: <font color="Red" ><b>{$total_services}</b></font></h2>
В том числе залогов на <b>{$total_zalog}</b>
<h2>Всего получено платежей на сумму: <font color="Red" ><b>{$total_payments}</b></font></h2>
<h2>ИТОГО БАЛАНС: <font color="Red" ><b>{$total_debet}</b></font></h2>
<br><br>
<table border="1">
	<Tr bgcolor="#cfd8df">
		<b>
		<td></td>
		<TD  width="100">Клиент</TD>
		<td>Счета фактур</td>
		<td>ИТОГО Услуг</td>
		<td>В том числе<br>залогов</td>
		<td>Платежи</td>
		<td>ИТОГО Оплата</td>
		<td>Дебет</td>
	</b>
		
		
	</Tr>
	{counter start=1 skip=1 print=false}
	{foreach from=$clients item=client key=key}
		{if ($client.sum neq 0) or ($client.total neq 0) }
	<tr>
		<td>{counter}</td>
		<TD width="100"><a href="?module=clients&id={$client.client}" target="_blank">{$client.client}</a><br>
		{$client.company_full}
		</TD>
		<TD>
			{foreach from=$client.invoice item=invoice}
				<a href="modules/accounts/view_inv.php?invoice_no={$invoice.invoice_no}&todo=invoice" target="_blank">
				{$invoice.invoice_no}</a> от {$invoice.invoice_date} сумма <b>{$invoice.sum_plus_tax}</b><br>
			{/foreach}
		</TD>
		<TD><b>{$client.sum}</b></TD>
		<td>{$client.zalog}</td>
		<TD>
			{foreach from=$client.payments item=pay}
				<a href="?module=accounts&action=accounts_payments&clients_client={$client.client}" target="_blank">{$pay.sum_rub}</a>
				 {$pay.payment_no} от {$pay.payment_date}<br>
			{/foreach}
		</TD>
		<TD><b> {$client.total}</b> </TD>
		<td>
			{if $client.debet < 0} <FONT color="Red">{$client.debet}</FONT>
			{else}{$client.debet}{/if}
		</td>
	</tr>
	{/if}	
	
	{/foreach}
</table>


