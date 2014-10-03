<TABLE class=price cellSpacing=3 cellPadding=1 border=0 width=100%>
<TR>
	<TD class=header vAlign=bottom>Номер платежа</TD>
	<TD class=header vAlign=bottom>Тип платежа</TD>
	<TD class=header vAlign=bottom>Клиент</TD>
	<TD class=header vAlign=bottom>Сумма платежа</TD>
	<TD class=header vAlign=bottom>Статус</TD>
	<TD class=header vAlign=bottom>Начало оплаты</TD>
	<TD class=header vAlign=bottom>Авторизация</TD>
	<TD class=header vAlign=bottom>Завершение</TD>
	<TD class=header vAlign=bottom>Отмена</TD>
	<TD class=header vAlign=bottom>&nbsp;</TD>
</TR>
{foreach from=$payments item=p}
<TR class={cycle values="even,odd"}>
	<TD>{$p.id}</TD>
	<TD>{$p.type}</TD>
	<TD>{$p.client_id}</TD>
	<TD>{$p.sum}</TD>
	<TD>{$p.status}</TD>
	<TD>{$p.datestart}</TD>
	<TD>{$p.dateauthorize}</TD>
	<TD>{$p.datepaid}</TD>
	<TD>{$p.datecancel}</TD>
	<TD>
		<a href="?module=clientaccounts&action=update_status&order_id={$p.id}">обновить</a>
		<a href="?module=clientaccounts&action=details&order_id={$p.id}" target="_blank">детали</a>
		<a href="?module=clientaccounts&action=cancel&order_id={$p.id}">отменить</a>
	</TD>
</TR>
{/foreach}