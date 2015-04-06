<H2>О пользователе</H2>
<H3>Информация о клиенте {$client.client}</H3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0><TBODY>
<TR><TD class=left width=30%>Компания:</TD><TD><input style='width:100%' name=company class=text value='{$client.company}'></TD></TR>
<TR><TD class=left>Полное название компании:</TD><TD><input style='width:100%' name=company_full class=text value='{$client.company_full}'></TD></TR>
{if isset($status)}
<TR><TD class=left>Статус:</TD><TD>
	<b>{$status.status_name}</b>, установлен {$status.ts|udate}
</TD></TR>{/if}
</TBODY></TABLE>

{if access('usercontrol','dealer')}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY><TR>
  <TD class=header vAlign=bottom width="15%">Клиент</TD>
  <TD class=header vAlign=bottom width="30%">Комментарий</TD>
  <TD class=header vAlign=bottom width="10%">Тел. подключения</TD>
  <TD class=header vAlign=bottom width="10%">Статус</TD>
  <TD class=header vAlign=bottom width="35%">Услуги</TD>
  </TR>
{foreach from=$clients item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD>{$item.company}</TD>
	<TD>{$item.dealer_comment}</TD>
	<TD>{$item.phone_connect}</TD>
	<TD>{$item.status_name}</TD>
	<TD>{foreach from=$item.services item=r name=inner}
		{$r.service_name} - {if $r.status=='working'}в работе{else}идёт подключение{/if}<br>
	{/foreach}</TD>
</TR>
{/foreach}
</TBODY></TABLE>
{/if}
