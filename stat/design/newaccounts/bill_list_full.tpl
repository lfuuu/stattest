<h2>Бухгалтерия {$fixclient} &nbsp;&nbsp;&nbsp;<span style='font-size:10px'>(<a href='?module=newaccounts&simple=1'>попроще</a>{if $fixclient_data.id_all4net} | <a href="http://all4net.ru/admin/users/balance.html?id={$fixclient_data.id_all4net}">all4net</a>{/if}{if $fixclient_data.type == 'multi'} | <a href="./?module=newaccounts&view_canceled={if $view_canceled}0{else}1{/if}">{if $view_canceled}Скрыть{else}Показать{/if} отказные счета</a>{/if})</span></h2>

<a href='{$LINK_START}module=newaccounts&action=bill_create'>Создать счёт</a> /
<a href='{$LINK_START}module=newaccounts&action=bill_balance'>Обновить баланс</a><br><br>
<span title='Клиент должен нам'>Входящее сальдо</span>: <form style='display:inline' action='?' method=post><input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=saldo><input type=text class=text style='width:70px;border:0;text-align:center' name=saldo value="{if isset($sum_cur.saldo)}{$sum_cur.saldo}{/if}"><input type=text class=text style='width:12px;border:0' readonly=1 value="{if $fixclient_data.currency=='USD'}${else}р{/if}">
 на дату <input type=text class=text style='width:85px;border:0' name=date value="{$sum_cur.ts}"><input type=submit class=button value='ok'></form> &nbsp; <a href='javascript:toggle2(document.getElementById("saldo_history"))'>&raquo;</a><br>
<table style='display:none;margin-left:20px' class=price id=saldo_history>
<TR><TD class=header>Дата изменения</td><TD class=header>Пользователь</td><TD class=header>Сальдо</td><TD class=header>Дата сальдо</td></TR>
{foreach from=$saldo_history item=item}
<TR class=even><td>{$item.edit_time}</td><td>{$item.user_name}</td><td>{$item.saldo} {if $item.currency=='USD'}${else}р{/if}</td><td>{$item.ts}</td></tr>
{/foreach}
</table>

<table width=100%>
<tr>
<td valign=top width=50%">

<Table width=100% border=0>
<tr style="background-color: #eaeaea;">
	<td>Всего залогов:</td>
	<td align=right> <b>{$sum_l.zalog.RUR|round:2} р.</b> </td>
	<td>/</td>
	<td align=right> <b>{$sum_l.zalog.USD|round:2} $</b> </td>
</tr>

<!--tr style="background-color: #eaeaea;">
	<td>Всего услуг и товаров:</td>
	<td align=right> <b>{$sum_l.service_and_goods.RUR|round:2} р.</b> </td>
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
	<td align=right> <b> {if $fixclient_data.currency=='USD'} {$sum.RUR.bill|round:2} р.{else}{$sum_cur.bill|round:2} р. {/if}</td>
	<td>/</td>
	<td align=right>{if $fixclient_data.currency=='USD'}{$sum_cur.bill|round:2} ${else} <b>{$sum.USD.bill|round:2} $</b>{/if}</td>
</tr>
{if $fixclient_data.status == 'distr' or $fixclient_data.status == 'operator'}
<tr>
	<td>По счетам:</td>
	<td align=right colspan="3"> <b>+{$bill_total_add.p} / {$bill_total_add.n} = {$bill_total_add.t}</b>
</tr>
{/if}
<tr  style="background-color: #eaeaea;">
	<td>Общая сумма <span title='Клиент должен нам'>долга</span> (с учётом сальдо):</td>
	<td align=right> <b>{if $fixclient_data.currency!='USD'} {$sum_cur.delta+$sum_cur.saldo|round:2}{else}{$sum.RUR.delta+$sum.RUR.saldo|round:2}{/if} р.</b>
	<td>/</td>
	<td align=right><b>{if $fixclient_data.currency=='USD'}{$sum_cur.delta+$sum_cur.saldo|round:2}{else}{$sum.USD.delta+$sum.USD.saldo|round:2}{/if} $</b></td>
</tr>


</table>
</td>
<td valign=top style="padding-left: 100px;" align=right>
<div>
	<form action="?" name="show_incomegoods" method="get">
	<input type="hidden" name="module" value="newaccounts">
	<input type="hidden" name="action" value="show_income_goods">
	<input id="with_income" type="checkbox" value="Y" name="show" {if $get_income_goods_on_bill_list}checked{/if} onchange="show_income_goods();">
	<label for="with_income">Показывать заказы поставищика</label>
	</form>
</div>
{if $counters}
    <table>
        <tr>
            <td>
                <span title="Баланс по счетам: {$fixclient_data.balance}
                VOIP расход в этом месяце: {math equation="y*-1" y=$counters.amount_sum}
                Выставленная в счете абонентка: {$subscr_counter->subscription_rt_last_month}
                Начисленная абонентка за текущей месяц: {math equation="y*-1" y=$subscr_counter->subscription_rt}">Реалтайм баланс: 
        {math equation='((b*-1)-c+s)*-1' 
                b=$fixclient_data.balance 
                c=$counters.amount_sum 
                s=$subscr_counter->subscription_rt_balance
        }</span>
                <br><br>
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
	<TD class=header vAlign=bottom colspan=3>Разбивка оплаты</td>
	<TD class=header vAlign=bottom rowspan=2>Привязка</td>
	<TD class=header vAlign=bottom rowspan=2>Документы</td>
</TR><TR>
	<TD class=header vAlign=bottom>Дата</TD>
	<TD class=header vAlign=bottom>Номер</TD>
	<TD class=header vAlign=bottom>Сумма</TD>
	<TD class=header vAlign=bottom title='положительные числа - мы должны клиенту, отрицательные - клиент нам'>разница</TD>
	<TD class=header vAlign=bottom>Сумма</TD>
	<TD class=header vAlign=bottom>Дата</TD>
	<TD class=header vAlign=bottom>Курс</TD>
	<TD class=header vAlign=bottom>Кто</TD>
	<TD class=header vAlign=bottom>разница</TD>
	<TD class=header vAlign=bottom>Сумма оплаты</TD>
	<TD class=header vAlign=bottom>Дата платежа</TD>
</TR>
{foreach from=$billops item=op key=key name=outer}
{count_comments v=$op}
{if isset($op.bill) && (($op.bill && $op.bill.currency!=$fixclient_data.currency) || (!$op.bill && (count($op.pays)==1) && !$op.pays.0.in_sum))}
{assign var=class value=other}
{else}
{cycle values="even,odd" assign=class}
{/if}
<TR class={$class}>
{if isset($op.bill) && $op.bill}
	<TD rowspan={$rowspan}{if $op.bill.postreg!="0000-00-00"} style='background-color:#FFFFD0'{/if}>{$op.bill.bill_date}</TD>
	<TD rowspan={$rowspan} class=pay{$op.bill.is_payed}><a href='{$LINK_START}module=newaccounts&action=bill_view&bill={$op.bill.bill_no}'>{$op.bill.bill_no}{if strlen($op.bill.bill_no_ext)}<br>({$op.bill.bill_no_ext}){/if}</a></TD>
	<TD rowspan={$rowspan} align=right>{$op.bill.sum} {if $op.bill.currency=='USD'}${else}р{/if}
	{if isset($op.bill.gen_bill_rur) && $op.bill.gen_bill_rur!=0}<br><span style='font-size:85%' title='Сумма счёта, {$op.bill.gen_bill_date}'>{$op.bill.gen_bill_rur} р</span>{/if}
	</TD>
{else}
	<TD colspan=3 rowspan={$rowspan}>&nbsp;</TD>
{/if}

<TD rowspan={$rowspan} align=right>{objCurrency op=$op obj='delta' currency=$fixclient_data.currency}</TD>

{if count($op.pays)}
	{foreach from=$op.pays item=pay key=keyin name=inner}
	{if $smarty.foreach.inner.iteration!=1}</TR><TR class={$class}>{/if}
	{if isset($pay.p_bill_no) && isset($op.bill.bill_no) && $pay.p_bill_no==$op.bill.bill_no}
	<TD>{objCurrency op=$op obj='pay_full' pay=$pay currency=$fixclient_data.currency}</TD>
	<TD style='font-size:85%'>{$pay.payment_date} - &#8470;{$pay.payment_no} /
		{if $pay.type=='bank'}b{elseif $pay.type=='prov'}p{elseif $pay.type=='neprov'}n{elseif $pay.type=='webmoney'}wm{elseif $pay.type=='yandex'}y{else}{$pay.type}{/if}
		{if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}
	</TD>
	<TD style='padding:0 0 0 0'>{if $op.bill.currency=='USD' && access('newaccounts_payments','edit')}<form style='display:inline' action='?'>
		<input type=hidden name=module value=newaccounts>
		<input type=hidden name=action value=pay_rate>
		<input type=hidden name=id value={$pay.id}>
		<input type=text title='После изменения нажмите Enter, чтобы сохранить' name=rate class=text style='width:57px;border:0' value='{$pay.payment_rate}'></form>
		{else}
			&nbsp;
		{/if}
		</TD>
	<TD align="right"><span title="{$pay.add_date}">{$pay.user_name}</span>{if (access('newaccounts_payments','delete') && $pay.type != 'ecash')}<a onclick="return confirm('Вы уверены?')" href="{$LINK_START}module=newaccounts&action=pay_delete&id={$pay.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Удалить"></a>{/if}</TD>
	{else}
	<TD colspan=4>&nbsp;</TD>
	{/if}
 	{if $smarty.foreach.inner.iteration==1}
 	<TD rowspan={$rowspan} align=right>{objCurrency op=$op obj='delta2' currency=$fixclient_data.currency}</TD>
 	{/if}

	{if isset($op.bill.bill_no) && isset($pay.bill_no) && $pay.bill_no==$op.bill.bill_no}
	{if $pay.payment_id|strpos:"-"}
		<TD style='{if $pay.p_bill_no!=$pay.bill_no}background:#e0e0ff;{/if}'>{objCurrency op=$op obj='pay2' pay=$pay currency=$fixclient_data.currency}</TD>
		<TD style='font-size:85%;{if $pay.p_bill_no!=$pay.bill_no}background:#e0e0ff;{/if}'>&#8470;{$pay.payment_id}</TD>
	{else}
		<TD style='{if $pay.p_bill_no!=$pay.bill_no}background:#e0e0ff;{/if}'>{objCurrency op=$op obj='pay2' pay=$pay currency=$fixclient_data.currency}</TD>
		<TD style='font-size:85%;{if $pay.p_bill_no!=$pay.bill_no}background:#e0e0ff;{/if}'>{$pay.payment_date} - &#8470;{$pay.payment_no} /
			{if $pay.type=='bank'}b({$pay.bank}){elseif $pay.type=='prov'}p{elseif $pay.type=='neprov'}n{elseif $pay.type=='webmoney'}wm{elseif $pay.type=='yandex'}y{else}{$pay.type}{/if}
			{if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}</TD>
	{/if}
	{else}
	<TD colspan=2>&nbsp;</TD>
	{/if}
	<TD style='padding:0 0 0 0'>
		{if ($op.delta>=0.01) && access('newaccounts_payments','edit')}<form name=paybill{$pay.id} style='display:inline;width:""' action='?'>
		<input type=hidden name=module value=newaccounts>
		<input type=hidden name=action value=pay_rebill>
		<input type=hidden name=pay value={$pay.id}>
		<select name=bill onchange='paybill{$pay.id}.submit()' class=text style='border:0;padding:0;margin:0;width:""'>
			<option></option>
{foreach from=$billops item=sop key=key name=innerselect}{if isset($sop.bill) && $sop.bill && ($sop.delta<0)}
			<option value='{$sop.bill.bill_no}'{if $pay.p_bill_vis_no==$sop.bill.bill_no} selected{/if}>{$sop.bill.bill_no}</option>
{/if}{/foreach}
		</select>
		</form>{/if}
	</TD>
    <td>
	{if isset($qrs[$op.bill.bill_no].11) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].11}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].11}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].11}" target=_blank title="Акт-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].11}"></a>А1<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].12) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].12}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].12}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].12}" target=_blank title="Акт-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].12}"></a>A2<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].21) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].21}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].21}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].21}" target=_blank title="УПД-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].21}"></a>У1<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].22) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].22}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].22}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].22}" target=_blank title="УПД-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].22}"></a>У2<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].23) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].23}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].23}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].23}" target=_blank title="УПД-3"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].23}"></a>У3{/if}
    </td>
	{if $pay.comment}
	</TR><TR class={$class}><TD colspan=4 class=comment>{$pay.comment|escape:"html"}</TD><TD colspan=2>&nbsp;</TD>{/if}
	{/foreach}
	{if $op.bill.comment}
	</TR><TR class={$class}><TD colspan=4 class=comment>{$op.bill.comment|escape:"html"}</TD><TD colspan=5>&nbsp;</TD><TD colspan=2>&nbsp;</TD>
	{/if}
{else}
        <TD colspan=8 rowspan=1>&nbsp;</TD>
    <td>
	{if isset($qrs[$op.bill.bill_no].11) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].11}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].11}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].11}" target=_blank title="Акт-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].11}"></a>А1<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].12) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].12}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].12}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].12}" target=_blank title="Акт-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].12}"></a>A2<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].21) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].21}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].21}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].21}" target=_blank title="УПД-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].21}"></a>У1<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].22) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].22}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].22}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].22}" target=_blank title="УПД-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].22}"></a>У2<br/>{/if}
	{if isset($qrs[$op.bill.bill_no].23) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].23}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].23}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].23}" target=_blank title="УПД-3"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].23}"></a>У3{/if}
    </td>

    {if isset($op.bill.comment) && $op.bill.comment}
        </TR>
        <TR class={$class}><TD colspan=6 class=comment>{$op.bill.comment|escape:"html"}</TD>
    {/if}
{/if}
</TR>
{if isset($op.switch_to_mcn) &&  $op.switch_to_mcn}
<tr>
    <td colspan=12 style="padding:0 0 0 0;margin: 0 0 0 0;background-color: #9edbf0;font-size: 8pt; text-align: center;">Мсн Телеком</td>
</tr>
{/if}

{/foreach}
</TABLE>
{if access('newaccounts_bills','del_docs')}
<script>
{literal}
	$(document).ready(function(){
		statlib.modules.newaccounts.bill_list_full.simple_tooltip(".del_doc" ,"tooltip");
	});
	function show_income_goods()
	{
		document.forms["show_incomegoods"].submit();
	}
{/literal}
</script>
{/if}
