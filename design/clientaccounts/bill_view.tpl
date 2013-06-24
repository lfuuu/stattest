<H3 style='font-size:110%;margin: 0px'>
<table border=0 width=100%>
	<tr>
		<td colspan="1">
{assign var="isClosed" value="0"}{if $tt_trouble && $tt_trouble.state_id == 20}{assign var="isClosed" value="1"}{/if}
{*if !$isClosed*}
{if $tt_trouble.trouble_name}{$tt_trouble.trouble_name}{else}Заказ{/if}{if $bill.is_rollback}-<b><u>возврат</u></b>{/if} <b style="font-weight: bold; font-size: large">{$bill.bill_no}</b> 
 </H3>


<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr class=even style='font-weight:bold'><!--td>&nbsp;</td--><td width="1%">артикул</td><td>что</td><td>период</td><td>сколько{if $cur_state && $cur_state == 17}/отгружено{/if}</td><td>цена</td><td>сумма</td><td>тип</td></tr>
{foreach from=$bill_lines item=item key=key name=outer}
<tr class='{cycle values="odd,even"}'>
<!-- td>
	{*if !$all4net_order_number}<a href='{$LINK_START}module=newaccounts&action=line_delete&bill={$bill.bill_no}&sort={$item.sort}'>
		<img class=icon src='{$IMAGES_PATH}icons/delete.gif' alt='Удалить строку'>
	</a>{/if*}
</td-->
<td align=left><span title="{$item.art|escape}">{$item.art|truncate:10}<br>

</span></td>
<td>
{if $item.service && $item.service != '1C'}<a target=_blank href='{$PATH_TO_ROOT}pop_services.php?table={$item.service}&id={$item.id_service}'>{/if}
{$item.item}
{if $item.service}</a>{/if}
</td>
<td><nobr>{$item.date_from}</nobr><br><nobr>{$item.date_to}</nobr>{if access('newaccounts_bills')}</a>{/if}</td>
<td>{$item.amount}{if $cur_state && $cur_state == 17}/<span {if $item.amount != $item.dispatch}style="font-weight: bold; color: #c40000;"{/if}>{$item.dispatch}{/if}</td>
<td align=right>{$item.price}</td>
<td align=right>{if $item.all4net_price<>0}{$item.all4net_price*$item.amount|round:2}{else}{if $bill_client.nds_zero}{$item.sum|round:2}{else}{$item.sum*1.18|round:2}{/if}{/if}</td>
<td>{$item.type}</td>
</tr>
{/foreach}
<tr>&nbsp;</td><td colspan=5 align=right><b>Итого: </b>&nbsp; </td><td align=right><b>{$bill.sum|round:2}</b></td></tr>
</TABLE>