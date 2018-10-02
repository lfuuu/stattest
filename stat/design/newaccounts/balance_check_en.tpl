{literal}
<script>
	function sendForm()
	{
		var act = $('#act').val();
		if (act == 'html') {
			$("#f_send").attr('target','_blank');
			$('#i_pdf').val('0');
			$('#i_fullscreen').val('1');
		} else if (act == 'pdf') {
			$("#f_send").attr('target','_blank');
			$('#i_pdf').val('1');
			$('#i_fullscreen').val('1');			
		} else {
			$('#i_pdf').val('0');
			$('#i_fullscreen').val('0');
			$("#f_send").attr('target','');
		}
		$("#f_send").submit();
	}
	function onchange_act(el) 
	{
		if($("#act").val() == 'pdf') {
			$("#ptp").show();
		} else {
			$("#ptp").hide();
		}
	}
</script>
{/literal}
<form style='display:inline' action='?' id="f_send">
<table>
	<tr>
		<td>От:<br><input type=text id="date_from" name=date_from value='{$date_from}' class=text style="width:100px;"></td>
		<td>До:<br><input type=text id="date_to" name=date_to value='{$date_to}' class=text style="width:100px;"></td>
		<td>Начальное сальдо:<br><input type=text name=saldo value='{$saldo}' class=text style='width:100px'></td>
		<td>Подпись:<br><select name='sign'>
			<option value=''>Без подписи</option>
			<option value='istomina'{if $sign == 'istomina'} selected{/if}>Истомина И.В.</option>
			<option value='director'{if $sign == 'director'} selected{/if}>Директор</option>
		</select></td>
		<td>Действие:<br><select id='act' onchange="onchange_act(this);"><option value='none'>Пересчет</option><option value='html'>HTML</option><option value='pdf'>PDF</option></select></td>
		<td><div id="ptp" style="display:none;">Верхний отступ:<br><input type='text' name='pdf_top_padding' value='2' style="width:100px;" /></div></td>
		<td><br><input type=button value='Поехали' class=button onclick=sendForm();></td>
	</tr>
</table>
	<input type=hidden name=module value=newaccounts>
	<input type=hidden name=action value=balance_check>



<!--Полный экран: <input type=checkbox name=fullscreen value='1'>-->



<input type=hidden name=fullscreen value='0' id="i_fullscreen" />
<input type=hidden name=is_pdf value='0' id="i_pdf" />

</form>
<h2>Financial statement for {$fixclient_data.client} account</h2>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<thead>
<tr ><td width=50% colspan=4>Based on date from {$firma.name}, {$currency|currency_symbol}</td><td width=50% colspan=4>Based on date from {$company_full}, {$currency|currency_symbol}</td></tr>
<tr><td width=4%>&#8470; N</td><td width=36%>Operations,<br>documents</td><td width=5%>Debit</td><td width=5%>Credit</td>
<td width=4%>&#8470; N</td><td width=24%>Operations,<br>documents</td><td width=11%>Debit</td><td width=11%>Credit</td></tr></thead><tbody>
{foreach from=$data item=item name=outer}
<tr{if !$fullscreen} class={cycle values="even,odd"}{/if}>
	<td>{$smarty.foreach.outer.iteration}</td>
	<td>{if $item.type=='saldo'}
			Balance on {$item.date|mdate:"d.m.Y"}
{elseif $item.type=='inv'}
	{if $item.inv_num == 3}
		The act of transferring equipment on bail
    {else}
		{if $item.inv_num!=4}
			Invoice
		{else}
			Waybill
		{/if}
	{/if} <nobr>({$item.date|mdate:"d.m.Y"},</nobr> <nobr>&#8470;{$item.inv_no})</nobr>
{elseif $item.type=='pay'}
	Payment <nobr>({$item.date|mdate:"d.m.Y"},</nobr> <nobr>&#8470;{$item.pay_no})</nobr>
{elseif $item.type=='creditnote'}
	Credit-node on <nobr>{$item.date|mdate:"d.m.Y"}</nobr>
{elseif $item.type=='total'}
	Period transactions
{/if}
</td>
	<td align=right>{if isset($item.sum_income) && ($item.sum_income || $item.type =='saldo')}{$item.sum_income|round:2|replace:".":","}{else}&nbsp;{/if}</td>
	<td align=right>{if isset($item.sum_outcome) && ($item.sum_outcome || $item.type =='saldo')}{$item.sum_outcome|round:2|replace:".":","}{else}&nbsp;{/if}</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/foreach}
</tbody></table>

<font style="color: black;">Based on date from {$firma.name} on {$date_to_val|mdate:"d.m.Y г."},

{if $zalog} с учетом платежей полученных в обеспечение исполнения обязательств по договору:
<table>
{foreach from=$zalog item=z name=zalog}
<tr><td>{$smarty.foreach.zalog.iteration}.&nbsp;</td><td>{$z.date|mdate:"d.m.Y"}, &#8470;{$z.inv_no} ({$z.items})</td><td>{$z.sum_income|money_currency:$currency}</td></tr>
{/foreach}
</table>

{else}

{/if}

{if $ressaldo.sum_income>0.0001}
	the indebtedness to {$firma.name} is {$ressaldo.sum_income|money_currency:$currency}
{elseif $ressaldo.sum_outcome>0.0001}
	the indebtedness to {$company_full} is {$ressaldo.sum_outcome|money_currency:$currency}
{else}
	is no debt
{/if}
</font>
<script>
optools.DatePickerInit();
</script>