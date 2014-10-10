<h1>Отчет по услугам за месяц <b>{$period}</b> Компания: <b>{$firma}</b></h1>

Курс доллара на последний день месяца установлен {$rate}<br>

<table>
<tr>
	<TD colspan="2" align="left" bgcolor="#EE7857">Услуги</TD><td>&nbsp;&nbsp;</td><TD colspan="2" align="right" bgcolor="#EEDE78">Деньги</TD>
</tr>
<tr>
	<td bgcolor="#FFD6D6">Абонентская плата</td><TD bgcolor="#FFD6D6" align="right"><b>{$total.ab}</b></TD><td>&nbsp;&nbsp;</td>
	<td bgcolor="#FFFFD8">На расчетный счет</td><TD bgcolor="#FFFFD8">{$total.payments.bnal}</TD>
</tr>
<tr>
	<TD bgcolor="#FFD6D6">Превышение трафика</td><TD bgcolor="#FFD6D6" align="right"><b>{$total.traf}</b></TD><td>&nbsp;&nbsp;</td>
	<td bgcolor="#FFFFD8">Касса</td><TD bgcolor="#FFFFD8">{$total.payments.nal}</TD>
	
</tr>
<tr>
	<td bgcolor="#FFD6D6">Подключение новых услуг</td><TD bgcolor="#FFD6D6" align="right"> <b>{$total.new}</b></TD><td>&nbsp;&nbsp;</td>
	<td bgcolor="#FFFFD8">Другое</td><TD bgcolor="#FFFFD8">{$total.payments.black}</TD>
	
</tr>
<tr>
	<td bgcolor="#FFD6D6">Другие услуги</td><TD bgcolor="#FFD6D6" align="right"><b>{$total.else}</b></TD><td>&nbsp;&nbsp;</td><td></td><TD></TD>
	
</tr>
<tr>
	<TD bgcolor="#FFD6D6">Залоги за оборудование</td><td bgcolor="#FFD6D6" align="right"><b>{$total.zalog}</b></td><td>&nbsp;&nbsp;</td><td></td><TD></TD>
	
</tr>

<tr>
	<td bgcolor="#EE7857"><b>ИТОГО</b></td><TD bgcolor="#EE7857">{$total.total}</TD><td>&nbsp;&nbsp;</td>
	<td bgcolor="#EEDE78"><b>ИТОГО</b></td><TD  bgcolor="#EEDE78">{$total.payments.total}</td>
	
</tr>




	
</table>

<br>
<hr>
<table cellpadding="10" cellspacing="10">
	<TR>
		<TD bgcolor="#EE7857">Баланс</TD>
		<TD bgcolor="#EEDE78"><b>{$balance}</b></TD>
	</TR>
</table>

<br>
<hr>



<table border="1">
	<tr bgcolor="#cfd8df">
	
		
		<TD >Логин</TD>
		<TD> Фирма </td>
		<td>Абонентская плата</td>
		<td>Трафик</td>
		<td>Подключение</td>
		<td>Другие услуги</td>
		<td>Залог</td>
		<TD>Всего услуг на сумму</TD>
		<td>На расчетный счет</td>
		<td>Касса</td>
		<td>Другое</td>
		<td>Всего</td>
	</tr>
	
	{foreach from=$clients item=client key=key}
	<tr>
		<TD><A target="_blank" href="?module=accounts&action=accounts_payments&clients_client={$client.client}">{$client.client}</A></TD>
		<TD>{$client.company_full}</TD>
		<TD>{$client.services.ab}</TD>
		<TD>{$client.services.traff}</TD>
		<TD>{$client.services.new}</TD>
		<TD>{$client.services.else}</TD>
		<TD>{$client.services.zalog}</TD>
		<TD bgcolor="#EE7857"><b>{$client.services.total}</b></TD>
		<TD>{$client.payments.bnal}</TD>
		<TD>{$client.payments.nal}</TD>
		<TD>{$client.payments.black}</TD>
		<TD bgcolor="#EEDE78"><b>{$client.payments.total}</b></TD>
	</tr>
	
	
	{/foreach}
</table>


