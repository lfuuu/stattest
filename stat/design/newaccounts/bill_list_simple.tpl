<h2>Бухгалтерия {$fixclient} &nbsp;&nbsp;&nbsp;<span style='font-size:10px'>(<a href='?module=newaccounts&simple=0'>посложнее</a>{if $fixclient_data.id_all4net} | <a href="http://all4net.ru/admin/users/balance.html?id={$fixclient_data.id_all4net}">all4net</a>{/if}{if $fixclient_data.type == 'multi'} | <a href="./?module=newaccounts&view_canceled={if $view_canceled}0{else}1{/if}">{if $view_canceled}Скрыть{else}Показать{/if} отказные счета</a>{/if})</span></h2>

<a href='{$LINK_START}module=newaccounts&action=bill_create'>Создать счёт</a> /
<a href='{$LINK_START}module=newaccounts&action=bill_balance'>Обновить баланс</a> <br><br>
<span title='Клиент должен нам'>Входящее сальдо</span>: <form style='display:inline' action='?' method=post><input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=saldo><input type=text class=text style='width:70px;border:0;text-align:center' name=saldo value="{if isset($sum_cur.saldo)}{$sum_cur.saldo}{/if}"><input type=text class=text style='width:12px;border:0' readonly=1 value="{if $fixclient_data.currency=='USD'}${else}р{/if}">
 на дату <input type=text class=text style='width:85px;border:0' name=date value="{$sum_cur.ts}"><input type=submit class=button value='ok'></form> &nbsp; <a href='javascript:toggle2(document.getElementById("saldo_history"))'>&raquo;</a><br>
<table style='display:none;margin-left:20px' class=price id=saldo_history>
<TR><TD class=header>Дата изменения</td><TD class=header>Пользователь</td><TD class=header>Сальдо</td><TD class=header>Дата сальдо</td></TR>
{foreach from=$saldo_history item=item}
<TR class=even><td>{$item.edit_time}</td><td>{$item.user_name}</td><td>{if isset($item.saldo)}{$item.saldo}{/if} {if $item.currency=='USD'}${else}р{/if}</td><td>{$item.ts}</td></tr>
{/foreach}
</table>

<table width=100%>
<tr>
<td valign=top width=50%">

<Table width=100% border=0>
<tr style="background-color: #eaeaea;">
	<td>Всего залогов:</td>
	<td align=right> <b>{$sum_l.zalog.RUB|round:2} р.</b> </td>
	<td>/</td>
	<td align=right> <b>{$sum_l.zalog.USD|round:2} $</b> </td>
</tr>

<!--tr style="background-color: #eaeaea;">
	<td>Всего услуг и товаров:</td>
	<td align=right> <b>{$sum_l.service_and_goods.RUB|round:2} р.</b> </td>
	<td>/</td>
	<td align=right> <b>{$sum_l.service_and_goods.USD|round:2} $</b></td>
</tr-->
<tr>
	<td>Всего платежей:</td>
	<td align=right> <b>{$sum_l.payments|round:2|default:'0.00'} р.</b></td>
	<td></td>
	<td></td>
</tr>

<tr  style="background-color: #eaeaea;">
	<td>Общая сумма оказанных услуг:</td>
	<td align=right> <b> {if $fixclient_data.currency=='USD'} {$sum.RUB.bill|round:2} р.{else}{$sum_cur.bill|round:2} р. {/if}</td>
	<td>/</td>
	<td align=right>{if $fixclient_data.currency=='USD'}{$sum_cur.bill|round:2} ${else} <b>{$sum.USD.bill|round:2} $</b>{/if}</td>
</tr>


<tr>
	<td>Общая сумма <span title='Клиент должен нам'>долга</span> (с учётом сальдо):</td>
    <td align=right> <b>
            {if $fixclient_data.currency!='USD'}
                {if isset($sum_cur.saldo)}{$sum_cur.delta+$sum_cur.saldo|round:2}{else}{$sum_cur.delta|round:2}{/if}
            {else}
                {if isset($sum.RUB.saldo)}{$sum.RUB.delta+$sum.RUB.saldo|round:2}{else}{$sum.RUB.delta|round:2}{/if}
            {/if} р.</b>
    </td>
    <td></td>
    <td align=right><b>
            {if $fixclient_data.currency=='USD'}
                {if isset($sum_cur.saldo)}{$sum_cur.delta+$sum_cur.saldo|round:2}{else}{$sum_cur.delta|round:2}{/if}
            {else}
                {if isset($sum.USD.saldo)}{$sum.USD.delta+$sum.USD.saldo|round:2}{else}{$sum.USD.delta|round:2}{/if}
            {/if} $</b>
    </td>
</tr>


</table>
</td>

<td valign=top style="padding-left: 100px;" align=right>
{if $counters}
<table>
<tr>
    <td>
        <b>IP-Телефония:</b><br/>
        Расход за день: <b>{$counters.amount_day_sum}</b><br/>
        Расход за месяц: <b>{$counters.amount_month_sum}</b><br/>
        Текущий баланс: <b>{$fixclient_data.balance-$counters.amount_sum} {$fixclient_data.currency}</b><br/>
    </td>
</tr>
</table>
{/if}
</td>
</tr>
</table>

<TABLE class=price cellSpacing=3 cellPadding=1 border=0 width=100%><TR>
	<TD class=header vAlign=bottom colspan=3>Счёт</td>
	<TD class=header vAlign=bottom>&nbsp;</td>
	<TD class=header vAlign=bottom colspan=4>Платёж</td>
</TR><TR>
	<TD class=header vAlign=bottom>Дата</TD>
	<TD class=header vAlign=bottom>Номер</TD>
	<TD class=header vAlign=bottom>Сумма</TD>
	<TD class=header vAlign=bottom title='положительные числа - мы должны клиенту, отрицательные - клиент нам'>разница</TD>
	<TD class=header vAlign=bottom>Сумма</TD>
	<TD class=header vAlign=bottom>Дата</TD>
	<TD class=header vAlign=bottom>Курс</TD>
	<TD class=header vAlign=bottom>Кто</TD>
</TR>
{foreach from=$billops item=op key=key name=outer}
{count_comments v=$op}
{if (isset($op.bill) && $op.bill && $op.bill.currency!=$fixclient_data.currency) || ((!isset($op.bill) || !$op.bill) && (count($op.pays)==1) && !$op.pays.0.in_sum)}
{assign var=class value=other}
{else}
{cycle values="even,odd" assign=class}
{/if}
<TR class={$class}>
{if isset($op.bill) && $op.bill}
	<TD rowspan={$rowspan}{if $op.bill.postreg!="0000-00-00"} style='background-color:#FFFFD0'{/if}>{$op.bill.bill_date}</TD>
	<TD rowspan={$rowspan} class=pay{$op.bill.is_payed}>
		<a href='{$LINK_START}module=newaccounts&action=bill_view&bill={$op.bill.bill_no}'>{$op.bill.bill_no}</a>
	</TD>
	<TD rowspan={$rowspan} align=right>{$op.bill.sum} {if $op.bill.currency=='USD'}${else}р{/if}
	{if $op.bill.gen_bill_rub!=0}<br><span style='font-size:85%' title='Сумма счёта, {$op.bill.gen_bill_date}'>{$op.bill.gen_bill_rub} р</span>{/if}
	</TD>
{else}
	<TD colspan=3 rowspan={$rowspan}>&nbsp;</TD>
{/if}

<TD rowspan={$rowspan} align=right>{objCurrency op=$op obj='delta' currency=$fixclient_data.currency simple=1}</TD>

{if count($op.pays)}
	{foreach from=$op.pays item=pay key=keyin name=inner}
	{if $smarty.foreach.inner.iteration!=1}</TR><TR class={$class}>{/if}
	<TD>
		{objCurrency op=$op obj='pay' pay=$pay currency=$fixclient_data.currency simple=1}
	</TD>
	<TD style='font-size:85%'>{$pay.payment_date} - &#8470;{$pay.payment_no} /
		{if $pay.type=='bank'}b({$pay.bank}){elseif $pay.type=='prov'}p{elseif $pay.type=='neprov'}n{elseif $pay.type=='webmoney'}wm{elseif $pay.type=='yandex'}y{else}{$pay.type}{/if}
		{if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}
	</TD>
	<TD style='padding:0 0 0 0;'>{if isset($op.bill) && $op.bill.currency=='USD'}{$pay.payment_rate}{else}&nbsp;{/if}</TD>
	<TD><span title="{$pay.add_date}">{$pay.user_name}</span></TD>

	{if $pay.comment}</TR><TR class={$class}><TD colspan=4 class=comment>{$pay.comment|escape:"html"}</TD>{/if}
	{/foreach}
	{if isset($op.bill) && $op.bill.comment}
	</TR><TR class={$class}><TD colspan=4 class=comment>{$op.bill.comment|escape:"html"}</TD><TD colspan=4>&nbsp;</TD>
	{/if}
{else}
	{if isset($op.bill) && $op.bill.comment}
		<TD colspan=4 rowspan=2>&nbsp;</TD>
		</TR><TR class={$class}><TD colspan=4 class=comment>{$op.bill.comment|escape:"html"}</TD>
	{else}
		<TD colspan=4>&nbsp;</TD>
	{/if}
{/if}
</TR>
{if isset($op.switch_to_mcn) && $op.switch_to_mcn}
<tr>
    <td colspan=12 style="padding:0 0 0 0;margin: 0 0 0 0;background-color: #9edbf0;font-size: 8pt; text-align: center;">Мсн Телеком</td>
</tr>
{/if}

{/foreach}
</TABLE>
