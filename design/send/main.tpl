<H2>Отправка счетов</H2>
{if isset($refresh) && ($refresh!=0)}{literal}
<script language=javascript>
function refrfunc(){
	window.location.reload();
};
window.setTimeout("javscript:refrfunc()",{/literal}{$refresh}{literal}000);
	
</script>
{/literal}{else}
{if access_action('send','send')}
<form style='display:inline;padding:0;margin:0' action='?' method=get>
Добавить счета всех клиентов в список	<input type=hidden name=module value=send>
	<input type=hidden name=action value=send>
	<select name=year><option value=2005>2005</option>
	<option value=2006>2006</option>
	<option value=2007 selected>2007</option>
	<option value=2008>2008</option>
	<option value=2009>2009</option>
	</select>
	<select name=month>
		<option value=1>январь</option>	
		<option value=2>февраль</option>	
		<option value=3>март</option>	
		<option value=4>апрель</option>	
		<option value=5>май</option>	
		<option value=6>июнь</option>	
		<option value=7>июль</option>	
		<option value=8>август</option>	
		<option value=9>сентябрь</option>	
		<option value=10>октябрь</option>	
		<option value=11>ноябрь</option>	
		<option value=12>декабрь</option>
	</select>
	<input type=hidden name=filter value=number><input type=submit class=submit value='ok'></form><br>
<br><br>
{/if}
{if access_action('send','process')}<a href='{$LINK_START}module=send&action=process&test=1'>Тестовая отправка счетов (10 штук)</a><br><br>
<a href='{$LINK_START}module=send&action=process&test=0'>Реальная отправка счетов (10 штук)</a><br>
<a href='{$LINK_START}module=send&action=process&test=0&cont=1'>Реальная отправка счетов (все)</a><br>
<br>{/if}
{/if}
<H3>Состояние</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="20%">Клиент</TD>
  <TD class=header vAlign=bottom width="20%">Счёт</TD>
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
	<TD><a href='modules/accounts/view.php?bill_no={$item.bill_no}&client={$item.client}'>{$item.bill_no}</a></TD>
	<TD{if (isset($item.cur_sent)) && ($item.cur_sent==1)} style='color:red;font-weight:bold'{/if}>{$item.state}</TD>
	<TD>{if $item.last_send!="0000-00-00 00:00:00"}{$item.last_send}{/if}</TD>
	<TD style='font-size:80%'>{$item.message}</TD>
</TR>
{/foreach}
{/foreach}
</TBODY></TABLE>
<br>
