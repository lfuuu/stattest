<h2>Импорт платежей</h2>
<form action='?' method=post>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=pi_apply>
<input type=hidden name=file value='{$file}'>
<input type=hidden name=bank value='{$bank}'>
<input type='checkbox' id='check_all' style='margin-left:4px' onclick='optools.pays.sel_all_pay_radio(event)'> Выбрать всех<br />
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0 id="pays_tbl">
{foreach from=$payments item=pay name=outer}
<tr bgcolor=#fffff5><td colspan='4'>
{if count($pay.clients)}
	{foreach from=$pay.clients item=i name=inner}
		<input type=radio name=pay[{$pay.id}][client] value='{$i.client}'{if $pay.imported} disabled='disabled'{/if} onclick = "filterBills('bills_{$pay.id}',GBILLS[{$i.id}],'{$pay.bill_no}')">
			<a href='{$LINK_START}module=clients&id={$i.id}'>{$i.client}</a> - 
			<span style='font-size:85%'>{$i.company_full} ({$i.manager})
			{if $i.is_ext} - {$i.comment}{else} - основной ИНН{/if}</span><br>
	{/foreach}
	{if !$pay.imported}
		<input type=radio name=pay[{$pay.id}][client] value=''>не вносить
	{/if}
{else}
	ИНН: {$pay.inn}<br>
{/if}
</td>
</tr>
<tr bgcolor={if $pay.imported}#FFE0E0{else}#EEDCA9{/if}><td>{if $pay.sum_rub > 0}<br><br>{/if}Платеж &#8470;{$pay.pp} от {$pay.date}
{if $pay.sum_rub > 0}<br><br><br><span style="font-size:7pt;" title="{$pay.payer|escape}">{$pay.payer|truncate:35}</span>{/if}
<input type=hidden name=pay[{$pay.id}][pay] value='{$pay.pp}'>
<input type=hidden name=pay[{$pay.id}][date] value='{$pay.date}'>
<input type=hidden name=pay[{$pay.id}][oper_date] value='{$pay.oper_date}'>
<input type=hidden name=pay[{$pay.id}][sum_rub] value='{$pay.sum_rub}'></td>
<td><b>{$pay.sum_rub}</b> р.</td><td>
    
{if $pay.client.client}
	<select name=pay[{$pay.id}][bill_no] id=bills_{$pay.id}>
	<option value=''>(без привязки)</option>
    {assign var='is_select' value=false}
	{foreach from=$clients_bills[$pay.client.id] item=bill name=inner2}
	<option value={$bill.bill_no}{if $pay.bill_no==$bill.bill_no} selected{assign var='is_select' value=true}{/if}>{$bill.bill_no}{if $bill.is_payed}+{/if}</option>
	{/foreach}
	{*if !$is_select && $pay.bill_no!==""}<option value={$pay.bill_no} selected>{$pay.bill_no}</option>{/if*}
	</select>
{else}
	<input type=text class=text name=pay[{$pay.id}][bill_no] style='width:100px'>
{/if}
<input type=text class=text name=pay[{$pay.id}][usd_rate] style='width:60px' value={$pay.usd_rate}>
</td><td width=50%>
{$pay.comment|escape:"html"}<br>
<textarea name=pay[{$pay.id}][comment] class=text style='width:100%;font-size:85%'></textarea>
</td></tr>
{/foreach}
</TABLE>
<br><b>Сумма занесенных: </b>{$payments_imported} руб.<br>
<br><b>Сумма +/-: </b>{$payments_plus} / {$payments_minus} руб.<br>
<br><b>Сумма итого: </b>{$payments_sum} руб.<br>
<DIV align=center><INPUT class=button type=submit value="Внести платежи"></DIV></FORM>
<script language=javascript>
{literal}
var GBILLS = {};
function filterBills(bid, data, billno) {
	var obj=document.getElementById(bid);
	for (var i=obj.childNodes.length-1;i>=0;i--) {
		obj.removeChild(obj.childNodes[i]);
	}
	if (billno) {
		var opt = document.createElement("OPTION");
		opt.innerHTML = billno;
		opt.value = billno;
		obj.appendChild(opt);
	}
	{
		var opt = document.createElement("OPTION");
		opt.innerHTML = "(без привязки)";
		opt.value = "";
		obj.appendChild(opt);
	}
	for (var i in data) {
		var opt = document.createElement("OPTION");
		opt.innerHTML = data[i];
		opt.value = data[i];
		obj.appendChild(opt);
	}
}
{/literal}

{foreach from=$clients_bills item=bills key=id name=outer}
	GBILLS[{$id}] = Array(); //{ldelim}{rdelim};
	{foreach from=$bills item=bill name=inner}
		GBILLS[{$id}].push ("{$bill.bill_no}");
	{/foreach}
{/foreach}
</script>
