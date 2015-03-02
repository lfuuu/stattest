<h2>Поиск счета</h2>
{if isset($bills)}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr><th>&nbsp;</th><th>клиент</th><th>компания</th><th>дата/н счёта</th><th>сумма счёта</th><th>&nbsp;</th></tr>
{foreach from=$bills item=item key=key name=outer}<tr class={cycle values="even,odd"}>
<td>{$smarty.foreach.outer.iteration}</td>
<td{if $item.nal=='nal'} bgcolor='#FFC0C0'{/if}><a href='{$LINK_START}module=newaccounts&action=bill_list&clients_client={$item.client}'>{$item.client}</a></td>
<td>{$item.company}</td>
<td>{$item.bill_date} - <a href='{$LINK_START}module=newaccounts&action=bill_view&bill={$item.bill_no}'>{$item.bill_no}</a></td>
<td align=right>{$item.sum|money:$item.currency}</td>
<td>{if $item.firma=='markomnet'}mar{else}mcn{/if}</td>
</tr>{/foreach}
</TABLE>
{/if}
