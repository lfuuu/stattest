<h2>Бухгалтерия {$fixclient} &nbsp;&nbsp;&nbsp;<span style='font-size:10px'>(<a href='?module=newaccounts&simple=1'>попроще</a>{if $fixclient_data.id_all4net} | <a href="http://all4net.ru/admin/users/balance.html?id={$fixclient_data.id_all4net}">all4net</a>{/if}{if $fixclient_data.type == 'multi'} | <a href="./?module=newaccounts&view_canceled={if $view_canceled}0{else}1{/if}">{if $view_canceled}Скрыть{else}Показать{/if} отказные счета</a>{/if})</span></h2>

<a href='{$LINK_START}module=newaccounts&action=bill_create'>Создать счёт</a>
<!--/ <a href='{$LINK_START}module=newaccounts&action=bill_create&currency={if $fixclient_data.currency=='USD'}RUR{else}USD{/if}'>в {if $fixclient_data.currency=='USD'}RUR{else}USD{/if}</a>--><br>
<a href='{$LINK_START}module=newaccounts&action=bill_balance'>Обновить баланс</a> / <a href='{$LINK_START}module=newaccounts&action=bill_balance&nosaldo=1'>без учёта сальдо</a><br>
<span title='Клиент должен нам'>Сальдо</span>: <form style='display:inline' action='?' method=post><input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=saldo><input type=text class=text style='width:70px;border:0;text-align:center' name=saldo value="{$sum_cur.saldo}"><input type=text class=text style='width:12px;border:0' readonly=1 value="{if $fixclient_data.currency=='USD'}${else}р{/if}">
 на дату <input type=text class=text style='width:85px;border:0' name=date value="{$sum_cur.ts}"><input type=submit class=button value='ok'></form> &nbsp; <a href='javascript:toggle2(document.getElementById("saldo_history"))'>&raquo;</a><br>
<table style='display:none;margin-left:20px' class=price id=saldo_history>
<TR><TD class=header>Дата изменения</td><TD class=header>Пользователь</td><TD class=header>Сальдо</td><TD class=header>Дата сальдо</td></TR>
{foreach from=$saldo_history item=item}
<TR class=even><td>{$item.edit_time}</td><td>{$item.user_name}</td><td>{$item.saldo} {if $item.currency=='USD'}${else}р{/if}</td><td>{$item.ts}</td></tr>
{/foreach}
</table>
Общая сумма оказанных услуг: <b>{$sum_cur.bill|round:2} {if $fixclient_data.currency=='USD'}$</b> и <b>{$sum.RUR.bill|round:2} р{else}р</b> и <b>{$sum.USD.bill|round:2} ${/if}</b><br>
Общая сумма <span title='Клиент должен нам'>долга</span>: <b>{$sum_cur.delta+$sum_cur.saldo|round:2} {if $fixclient_data.currency=='USD'}$</b> и <b>{$sum.RUR.delta+$sum.RUR.saldo|round:2} р{else}р</b> и <b>{$sum.USD.delta+$sum.USD.saldo|round:2} ${/if}</b> (с учётом сальдо)<br>
Real-time сальдо: <b>{$saldo_rt.RUR|round:2} р, {$saldo_rt.USD|round:2} $</b><br>
<span style='font-size:85%'>
	Общая сумма <span title='Клиент должен нам'>долга</span>: <b>{$sum_cur.delta|round:2} {if $fixclient_data.currency=='USD'}$</b> и <b>{$sum.RUR.delta|round:2} р{else}р</b> и <b>{$sum.USD.delta|round:2} ${/if}</b> (без учёта сальдо)<br>
</span>
<TABLE class=price cellSpacing=3 cellPadding=1 border=0 width=100%><TR>
	<TD class=header vAlign=bottom colspan=3>Счёт</td>
	<TD class=header vAlign=bottom>&nbsp;</td>
	<TD class=header vAlign=bottom colspan=6>Платёж</td>
</TR><TR>
	<TD class=header vAlign=bottom>Дата</TD>
	<TD class=header vAlign=bottom>Номер</TD>
	<TD class=header vAlign=bottom>Сумма</TD>
	<TD class=header vAlign=bottom title='положительные числа - мы должны клиенту, отрицательные - клиент нам'>разница</TD>
	<TD class=header vAlign=bottom>Сумма</TD>
	<TD class=header vAlign=bottom>Дата</TD>
	<TD class=header vAlign=bottom>Полная сумма</TD>
	<TD class=header vAlign=bottom>Курс</TD>
	<TD class=header vAlign=bottom>Кто</TD>
	<TD class=header vAlign=bottom>Привязка</TD>
</TR>
{foreach from=$billops item=op key=key name=outer}
{count_comments v=$op}
{if ($op.bill && $op.bill.currency!=$fixclient_data.currency) || (!$op.bill && (count($op.pays)==1) && !$op.pays.0.in_sum)}
{assign var=class value=other}
{else}
{cycle values="even,odd" assign=class}
{/if}
<TR class={$class}>
{if $op.bill}
	<TD rowspan={$rowspan}{if $op.bill.postreg!="0000-00-00"} style='background-color:#FFFFD0'{/if}>{$op.bill.bill_date}</TD>
	<TD rowspan={$rowspan} class=pay{$op.bill.is_payed}><a href='{$LINK_START}module=newaccounts&action=bill_view&bill={$op.bill.bill_no}'>{$op.bill.bill_no}{if strlen($op.bill.bill_no_ext)}<br>({$op.bill.bill_no_ext}){/if}</a></TD>
	<TD rowspan={$rowspan} align=right>{$op.bill.sum} {if $op.bill.currency=='USD'}${else}р{/if}
	{if $op.bill.gen_bill_rur!=0}<br><span style='font-size:85%' title='Сумма счёта, {$op.bill.gen_bill_date}'>{$op.bill.gen_bill_rur} р</span>{/if}
	</TD>
{else}
	<TD colspan=3 rowspan={$rowspan}>&nbsp;</TD>
{/if}

<TD rowspan={$rowspan} align=right>{objCurrency op=$op obj='delta' currency=$fixclient_data.currency}</TD>

{if count($op.pays)}
	{foreach from=$op.pays item=pay key=keyin name=inner}
	{if $smarty.foreach.inner.iteration!=1}</TR><TR class={$class}>{/if}
	<TD>{objCurrency op=$op obj='pay2' pay=$pay currency=$fixclient_data.currency}</TD>
	<TD style='font-size:85%;{if $pay.p_bill_no!=$pay.bill_no}background:#e0e0ff;{/if}'>{$pay.payment_date} - &#8470;{$pay.payment_no} /
		{if $pay.type=='bank'}b{elseif $pay.type=='prov'}p{elseif $pay.type=='neprov'}n{elseif $pay.type=='webmoney'}wm{elseif $pay.type=='yandex'}y{else}{$pay.type}{/if}
		{if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}
	</TD>
	<TD>{objCurrency op=$op obj='pay_full' pay=$pay currency=$fixclient_data.currency}</TD>
	<TD style='padding:0 0 0 0'>{if $op.bill.currency=='USD' && access('newaccounts_payments','edit')}<form style='display:inline' action='?'>
		<input type=hidden name=module value=newaccounts>
		<input type=hidden name=action value=pay_rate>
		<input type=hidden name=id value={$pay.id}>
		<input type=text title='После изменения нажмите Enter, чтобы сохранить' name=rate class=text style='width:57px;border:0' value='{$pay.payment_rate}'></form>
		{else}
			&nbsp;
		{/if}
		</TD>
	<TD><span title="{$pay.add_date}">{$pay.user_name}</span>{if access('newaccounts_payments','delete')}<a onclick="return confirm('Вы уверены?')" href="{$LINK_START}module=newaccounts&action=pay_delete&id={$pay.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Удалить"></a>{/if}
	</TD>
	<TD style='padding:0 0 0 0'>
		{if ($op.delta>=0.01) && access('newaccounts_payments','edit')}<form name=paybill{$pay.id} style='display:inline;width:""' action='?'>
		<input type=hidden name=module value=newaccounts>
		<input type=hidden name=action value=pay_rebill>
		<input type=hidden name=pay value={$pay.id}>
		<select name=bill onchange='paybill{$pay.id}.submit()' class=text style='border:0;padding:0;margin:0;width:""'>
			<option></option>
{foreach from=$billops item=sop key=key name=innerselect}{if ($sop.bill) && ($sop.delta<0)}
			<option value='{$sop.bill.bill_no}'{if $pay.bill_vis_no==$sop.bill.bill_no} selected{/if}>{$sop.bill.bill_no}</option>
{/if}{/foreach}
		</select>
		</form>{/if}
	</TD>
	{if $pay.comment}</TR><TR class={$class}><TD colspan=5 class=comment>{$pay.comment|escape:"html"}</TD>{/if}
	{/foreach}
	{if $op.bill.comment}
	</TR><TR class={$class}><TD colspan=4 class=comment>{$op.bill.comment|escape:"html"}</TD><TD colspan=5>&nbsp;</TD>
	{/if}
{else}
{if $op.bill.comment}
	<TD colspan=5 rowspan=2>&nbsp;</TD></TR>
	<TR class={$class}><TD colspan=4 class=comment>{$op.bill.comment|escape:"html"}</TD>
{else}
	<TD colspan=5>&nbsp;</TD>
{/if}
{/if}
</TR>
{/foreach}
</TABLE>
