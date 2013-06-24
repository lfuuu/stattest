<h2>Счета</h2>
Общая сумма: <b>{$acc_sum}</b>
<table cellpadding="10" cellspacing="0" border="1">
<tr>
	<td></td>
	<td>Номер счета</td>
	<td>Клиент</td>
	<td>Компания</td>
	<td>Сумма</td>
	<td>Дата счета</td>
	<td>тип счёта</td>
	<td>&nbsp;</td>	
</tr>

{foreach from=$bills item=bill key=key}
	
        <tr {if $bill.must_pay==0} bgcolor="#F0F0F0" {else}{if $bill.state  ==  'ready'} bgcolor="#FFFFD8" {/if}{/if}>
        	<td>{$key+1}</td>
        	<td>{if $bill.state eq 'cancelled'} <FONT style="text-decoration : line-through;">{/if}
        		<a href="modules/{$module}/view.php?bill_no={$bill.bill_no}&client={$bill.client}&date={$bill.date}" target="_blank">{$bill.bill_no}</a>
       		{if $bill.state eq 'cancelled'} </FONT>{/if}	
        	</td> 
        	<td>{if $bill.state eq 'cancelled'} <FONT style="text-decoration : line-through;">{/if}
        	<a href="index.php?module=clients&id={$bill.client}">{$bill.client}</a>
        	{if $bill.state eq 'cancelled'} </FONT>{/if}
        	</td> 
        	<td>{if $bill.state eq 'cancelled'} <FONT style="text-decoration : line-through;">{/if}
        	<a href="index.php?module=clients&id={$bill.client}">{$bill.company}</a>
        	{if $bill.state eq 'cancelled'} </FONT>{/if}
        	</td> 
        	<td>{if $bill.state eq 'cancelled'} <FONT style="text-decoration : line-through;">{/if}
        	{$bill.sum}
        	{if $bill.state eq 'cancelled'} </FONT>{/if}
        	</td>
        	<td>{if $bill.state eq 'cancelled'} <FONT style="text-decoration : line-through;">{/if}
        	{$bill.date}
        	{if $bill.state eq 'cancelled'} </FONT>{/if}
        	</td>
        	<td>{$bill.type}</td>
			<td>
				<a href="modules/{$module}/send.php?bill_no={$bill.bill_no}&client={$bill.client}&date={$bill.date}" target="_blank">выслать</a>
			</td>
        </tr>
        


{/foreach}

</table>
