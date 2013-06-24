<H2>Отправка информации о превышении траффика</H2>
{if isset($refresh) && ($refresh!=0)}{literal}
<script language=javascript>
function refrfunc(){
	window.location.reload();
};
window.setTimeout("javscript:refrfunc()",{/literal}{$refresh}{literal}000);
	
</script>
{/literal}{else}
<a href='{$LINK_START}module=stats&action=send_process&test=1'>Тестовая отправка счетов (5 штук)</a><br><br>
<a href='{$LINK_START}module=stats&action=send_process&test=0'>Реальная отправка счетов (5 штук)</a><br>
<a href='{$LINK_START}module=stats&action=send_process&test=0&cont=1'>Реальная отправка счетов (все)</a><br>
<br>
{/if}
<H3>Состояние</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="20%">Клиент</TD>
  <TD class=header vAlign=bottom width="20%">Скачано</TD>
  <TD class=header vAlign=bottom width="20%">Дата отчёта</TD>
  <TD class=header vAlign=bottom width="10%">Состояние</TD>
  <TD class=header vAlign=bottom width="15%">Дата отправки</TD>
  <TD class=header valign=bottom>Сообщение об ошибке, если есть</td>
  </TR>
{foreach from=$send_clients item=send_client name=outer}
{foreach from=$send_client item=item name=inner}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
{if $smarty.foreach.inner.iteration==1}
	<TD rowspan='{count_rows_func start=0 arr=$send_client}'><a href='{$LINK_START}module=clients&id={$item.client}'>{$item.client}</a></TD>
{/if}
	<TD>{fsize value=$item.bytes}</TD>
	<TD>{$item.year}-{$item.month}</TD>
	<TD{if (isset($item.cur_sent)) && ($item.cur_sent==1)} style='color:red;font-weight:bold'{/if}>{$item.state}</TD>
	<TD>{if $item.last_send!="0000-00-00 00:00:00"}{$item.last_send}{/if}</TD>
	<TD style='font-size:80%'>{$item.message}</TD>
</TR>
{/foreach}
{/foreach}
</TBODY></TABLE>
<br>
