<h2>Баланс по счетам</h2>
<form action='?' method=get>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value="{$action}">
<label for="r1">По менеджеру: </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name="user_type" id="r1" value="manager"{if $user_type == 'manager'} checked{/if}><br />
<label for="r2">По создателю счета: </label><input type=radio name="user_type" id="r2" value="creator"{if $user_type != 'manager'} checked{/if}><font style="font-size: 8pt;">(так же последний редактировавший)</font><br />
<label for="r3">Сводный: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label><input type=radio name="user_type" id="r3" value="union"{if $user_type == 'union'} checked{/if}><font style="font-size: 8pt;">(по менеджеру+по создателю счета)</font><br />
<br />
<label for="r4">По дате создания счета&nbsp;&nbsp;</label><input type=radio name="report_by" value="bill_created" id="r4"{if $report_by == "bill_created"} checked{/if}><br>
<label for="r5">По дате закрытия счета&nbsp;&nbsp;</label><input type=radio name="report_by" value="bill_closed" id="r5"{if $report_by != "bill_created"} checked{/if}><br>

<SELECT name=manager><option value=''>не определено</option>{foreach from=$users_manager item=item key=user}<option value='{$item.user}' {if isset($item.selected)}{$item.selected}{/if}>{$item.name} ({$item.user})</option>{/foreach}</select>
От <input type=text class=text name='date_from' id='date_from' value='{$date_from}'> до <input type=text class=text id='date_to' name='date_to' value='{$date_to}'><br>
<input type=checkbox{if $b_nedopay} checked{/if} name=b_nedopay value=1>Оплаченные не полностью счета |
Недоплата больше, чем рублей: <input type='text' name='p_nedopay' size=5 value='{if $p_nedopay}{$p_nedopay}{else}1{/if}'><br>
<input type=checkbox{if $b_pay0} checked{/if} name=b_pay0 value=1>Показывать неоплаченные счета<br>
<input type=checkbox{if $b_pay1} checked{/if} name=b_pay1 value=1>Показывать оплаченные счета<br>
<br><br>
<input type=checkbox{if $b_show_bonus} checked{/if} name=b_show_bonus value=1>Расчет бонусов<br>
<br><br>

Статусы клиентов:<br>
{html_checkboxes name="cl_status" options=$l_status selected=$cl_status separator="<br />"}
<input type=submit class=button value='Просмотр'>
</form>
{if isset($bills)}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr><th>&nbsp;</th><th>клиент</th><th>компания</th><th>дата/н счёта</th><th>счёт</th>{if $b_show_bonus}<th>бонус</th>{/if}<th title="Менеджер клиента">М. клиента</th><th title="Менеджер счета">М. счета</th><th>&nbsp;</th></tr>
{foreach from=$bills item=item key=key name=outer}<tr class={cycle values="even,odd"}>
<td>{$smarty.foreach.outer.iteration}</td>
<td{if $item.nal=='nal'} bgcolor='#FFC0C0'{else}{if $item.nal=='prov'} bgcolor='#C0C0FF'{/if}{/if}><a href='{$LINK_START}module=newaccounts&action=bill_list&clients_client={$item.client}'>{$item.client}</a></td>
<td style='font-size:85%'>{$item.company}</td>
<td{if !$item.f_currency} style="background-color: #c0ffc0;"{/if}>{$item.bill_date} - <a href='{$LINK_START}module=newaccounts&action=bill_view&bill={$item.bill_no}'>{$item.bill_no}</a></td>
<td align=right>{$item.sum|money:$item.currency}</td>
{if $b_show_bonus}<td title="{$item.bonus_info|escape}">{$item.bonus}</td>{/if}
<td>{$item.client_manager}</td>
<td>{$item.bill_manager}</td>
<td>{if $item.firma=='markomnet'}mar{else}mcn{/if}</td>
</tr>{/foreach}

<tr style='background:#FFFFFF'>
<td colspan=2 align=left><b>Всего клиентов:</b></td>
<td align=left>{$clients_count}</td>
<td align=right><b>Итого:</b></td>
<td align=right>
	{foreach from=$totalAmount item=amount key=currency}
		<b>{$amount|money:$currency}</b><br/>
	{/foreach}
</td>
{if $b_show_bonus}
	<td align=center>
		{foreach from=$totalBonus item=amount key=currency}
			<b>{$amount|money:$currency}</b><br/>
		{/foreach}
	</td>
{/if}
</tr>
</TABLE>
{/if}
<script>
optools.DatePickerInit();
</script>
