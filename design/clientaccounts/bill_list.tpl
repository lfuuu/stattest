<h2>Счета {$fixclient}</h2>
Баланс RUR: <b>{$client.balance|round:2}</b><br>
Баланс USD: <b>{$client.balance_usd|round:2}</b><br>
Общая сумма оказанных услуг: <b>{$sum_cur.bill|round:2} {if $fixclient_data.currency=='USD'}$ {$sum.RUR.bill|round:2} р{else}р {$sum.USD.bill|round:2} ${/if}</b><br>
Общая сумма <span title='Клиент должен нам'>долга</span>: <b>{$sum_cur.delta+$sum_cur.saldo|round:2} {if $fixclient_data.currency=='USD'}$</b> и <b>{$sum.RUR.delta+$sum.RUR.saldo|round:2} р{else}р</b> и <b>{$sum.USD.delta+$sum.USD.saldo|round:2} ${/if}</b> (с учётом сальдо)<br>
Real-time сальдо: <b>{if $fixclient_data.currency=='RUR'}{$saldo_rt.RUR|round:2} р{else}{$saldo_rt.USD|round:2} ${/if}</b><br>
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
	<TD {if $op.bill.postreg!="0000-00-00"} style='background-color:#FFFFD0'{/if}>{$op.bill.bill_date}</TD>
	<TD class=pay{$op.bill.is_payed}>
		<a href='{$LINK_START}module=clientaccounts&action=bill_view&bill={$op.bill.bill_no}'>{$op.bill.bill_no}</a>
	</TD>
	<TD align=right>{$op.bill.sum} {if $op.bill.currency=='USD'}${else}р{/if}
	{if $op.bill.gen_bill_rur!=0}<br><span style='font-size:85%' title='Сумма счёта, {$op.bill.gen_bill_date}'>{$op.bill.gen_bill_rur} р</span>{/if}
	</TD>
{else}
	<TD colspan=3 rowspan={$rowspan}>&nbsp;</TD>
{/if}

<TD align=right>{objCurrency op=$op obj='delta' currency=$fixclient_data.currency simple=1}</TD>

{if count($op.pays)}
	{foreach from=$op.pays item=pay key=keyin name=inner}
	{if $smarty.foreach.inner.iteration!=1}</TR><TR class={$class}>{/if}
	<TD>
		{objCurrency op=$op obj='pay' pay=$pay currency=$fixclient_data.currency simple=1}
	</TD>
	<TD style='font-size:85%'>{$pay.payment_date} - &#8470;{$pay.payment_no} / 
		{if $pay.type=='bank'}b{elseif $pay.type=='prov'}p{elseif $pay.type=='neprov'}n{elseif $pay.type=='webmoney'}wm{elseif $pay.type=='yandex'}y{else}{$pay.type}{/if}
		{if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}
	</TD>
	{/foreach}
{else}
	<TD colspan=2>&nbsp;</TD>
{/if}
</TR>
{/foreach}
</TABLE>
