<h1>Отчет по балансу с клиентами</h1>

<form action="?" method="GET" name="balanceform">
<SELECT name="manager">
  <option value="pma">pma</option>
  <option value="bnv">bnv</option>
  <option value="all">Все</option>
</SELECT>
От <INPUT type="text" name="date_balanceF" value="{$date_balanceF}" size="10">
До <INPUT type="text" name="date_balanceT" value="{$date_balanceT}" size="10">
<INPUT type="submit" value="Показать">
<INPUT type="hidden" name="module" value="accounts">
<INPUT type="hidden" name="action" value="accounts_report_balance">
<INPUT type="hidden" name="client" value="{$client}">
<INPUT type="hidden" name="todo" value="filtr">
</form>

<table border="1">
 <tr bgcolor="#CFD8DF">
 	<TD><b>Общий баланс</b></TD>
 	<TD colspan="4"><font color="Red">{$total_balance}</font></TD>
 </tr>
 <tr bgcolor="#CFD8DF">
 	<TD><b>Общий долг</b></TD>
 	<TD colspan="4"><font color="Red">{$total_debet}</font></TD>
 </tr>
	<TR bgcolor="#CFD8DF">
		<TD></TD>
		<TD>Клиент</TD>
		<td>Название компании</td>
		<TD>Баланс по таблице</TD>
		<TD>Баланс клиента</TD>
		<TD>
		<TABLE cellspacing="0" border="0" cellpadding="0">
		<tr>
			<td width="100">Дата последней сверки</td>
			<TD width="50">Баланс на дату сверки</TD>
			<TD width="100"></TD>
		</tr>
		</table>
		</TD>
		
	</TR>
	{foreach from=$balance item=client key=key}
	<TR {if $client.data.nal eq 'nal'} bgcolor="#FF9B9B"{/if} >
		<TD>{$key+1}</TD>
		<TD><A href="?module=accounts&action=accounts_report_balance&client={$client.data.client}&todo=show_payments" 
		       target="_blank">{$client.data.client}</A>
		</TD>
		<TD>{$client.data.firma}</TD>
		<TD>{$client.data.balance_table}</TD>
		<TD>{if $client.data.saldo >= 0 }{$client.data.saldo}{else}<FONT color="Red">{$client.data.saldo}</FONT>{/if}
		</TD>
		<td>
		<FORM action="modules/accounts/update_saldo.php?clients_client={$client.data.client}" method="POST" target="_blank">
  			<TABLE cellspacing="0" border="0" cellpadding="0">
  			<tr>
  				<TD width="100">
  					<INPUT type="text" name="date_last_saldo" value="{$client.data.date_saldo}" size="10">
  				</TD>
  				<TD width="50">
  					<INPUT type="text" name="sum" value="{$client.data.sum}" size="10">
  				</TD>
  				<TD width="100">
  					<INPUT type="submit" name="submit" value="Обновить сальдо">
  				</TD>
  			</tr>
    
  			</TABLE>
		</FORM>		
		</td>
	</TR>
	{/foreach}
</table>