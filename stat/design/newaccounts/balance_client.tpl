<h2>Баланс с клиентами</h2>
<form action='?' method=get>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=balance_client>
Менеджер: <SELECT name=manager><option value=''>не определено</option><option value='()'>любой</option>{foreach from=$users_manager item=item key=user}<option value='{$item.user}'{$item.selected}>{$item.name} ({$item.user})</option>{/foreach}</select><br>
<input type=checkbox value=1 name=cl_off{if $cl_off} checked{/if}>Показывать отключенных клиентов<br>
<input type=checkbox value=1 name=cl_mar{if $cl_mar} checked{/if}>Показывать только клиентов у "Маркомнет"<br>
Сортировка: <SELECT name=sort><option value='0'{if $sort==0} selected{/if}>по логину</option><option value='1'{if $sort==1} selected{/if}>по долгу</option><option value='2'{if $sort==2} selected{/if}>по сумме платежей</option></select><br>
<input type=submit class=button value='Просмотр'>
</form>
{if isset($balance)}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr><th>&nbsp;</th><th>клиент</th><th>название компании</th><th>сумма долга</th><th>платежи</th><th>счета</th><th style='font-size:85%'>дата сальдо</th><th>фирма</th></tr>
{foreach from=$balance item=item key=key name=outer}<tr class={cycle values="even,odd"}>
<td>{$smarty.foreach.outer.iteration}</td>
<td{if $item.nal!='beznal'} bgcolor='#{if $item.nal == "nal"}FFC0C0{else}C0C0FF{/if}'{/if}><a href='{$LINK_START}module=newaccounts&action=bill_list&clients_client={$item.client}'>{$item.client}</a></td>
<td>{$item.company}</td>
<td align=right>{$item.saldo_sum+$item.sum_bills-$item.sum_payments|money:$item.currency}</td>
<td align=right>{$item.sum_payments|money:$item.currency}</td>
<td align=right>{$item.sum_bills|money:$item.currency}</td>
<td style='font-size:85%'>{$item.saldo_ts}</td>
<td>{if $item.firma=='markomnet'}mar{else}mcn{/if}</td>
</tr>{/foreach}
</TABLE>
{/if}
