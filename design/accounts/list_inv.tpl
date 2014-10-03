<h2>Счета-фактуры  для клиента {$customer}</h2>
Общая сумма: <b>{$acc_sum}</b>
<table cellpadding="10" cellspacing="0" border="1">
<tr>
	<td rowspan=2></td>
	<td rowspan=2>Номер счета-фактуры</td>
	<td rowspan=2>Дата</td>
	<td colspan=3>Сумма</td>
	<td rowspan=2>По счету N</td>
	<td rowspan=2>Платеж</td>
	<td rowspan=2></td>
</tr>
<tr><td>по с/ф</td><td>по платежам</td><td>разница</td></tr>
{assign var=key value=1}
{foreach name=invoices from=$inv item=bitem}
	{cycle values="#E4E3D2,#CFD8DF" assign="color"}
	{foreach from=$bitem.data item=item name=inner}
        <tr bgcolor="{$color}">
        	<td>{$key}</td>
        	<td>
        		<a href="modules/{$module}/view_inv.php?invoice_no={$item.invoice_no}&todo=invoice" target="_blank">{$item.invoice_no}</a> (<a href="modules/{$module}/view_inv.php?invoice_no={$item.invoice_no}&todo=akt" target="_blank">акт</a>)
        	</td>
        	<td>{$item.invoice_date}</td>
        	{if $smarty.foreach.inner.iteration == 1}
        	<td rowspan={$bitem.count}>{$bitem.sum}</td>
        	<td rowspan={$bitem.count}>{$item.sum_pay}</td>
        	
        	<td rowspan={$bitem.count}>
        		{if access('accounts_payments','correct')}
        			<form style='padding:0 0 0 0;margin:0 0 0 0' target="_blank" action='modules/{$module}/correct.php'>
        			<input type=hidden name=bill_no value='{$item.bill_no}'><input type=hidden name=client value='{$item.client}'>
        			<input type=textbox name=sum value="{$bitem.sum-$item.sum_pay|round:10}" class=text style='width:100px'>
        			<input type=submit class=button value='исправить' style='width:70px'>
        			</form>
        		{else}
        			{$bitem.sum-$item.sum_pay|round:10}
        		{/if}
        			</td>
        	<td rowspan={$bitem.count}><a href="modules/{$module}/view.php?bill_no={$item.bill_no}&client={$item.client}" target="_blank">{$item.bill_no}</a></td>
        	<td rowspan={$bitem.count}>N{$item.pay_no} от {$item.pay_date}</td>
        	{/if}
        	<td><a href="modules/{$module}/send_inv.php?invoice_no={$item.invoice_no}" target="_blank">Отправить</a></td>
        </tr>
	{assign var=key value=$key+1}
	{/foreach}

{/foreach}

</table>
