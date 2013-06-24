<h1>Просмотр таблицы баланса</h1>

<table border=1 cellspacing=0 cellpadding=4>
	<tr><td>клиент</td><td>компания</td><td>сумма</td></tr>
	{foreach from=$balance item=item key=key}
	<TR>
		<TD><A href="?module=accounts&action=accounts_report_balance&client={$item.client}&todo=show_payments" 
		       target="_blank">{$item.client}</A>
		</TD>
		<td>{$item.company}</td>
		<TD>{$item.sum}</td>
	</TR>
	{/foreach}
</table>