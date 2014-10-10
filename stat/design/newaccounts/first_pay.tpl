<h2>Первые платежи</h2>
<form action='?' method=get>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=first_pay>

С: <input type=text name='date_from' id='date_from' value="{$date_from}">
По: <input type=text name='date_to' id='date_to' value="{$date_to}">
Сортировка: <SELECT name=sort><option value='channel'{if $sort=='channel'} selected{/if}>по каналу продаж</option><option value='manager'{if $sort=='manager'} selected{/if}>по менеджеру</option><option value='client'{if $sort=='client'} selected{/if}>по клиенту</option></select>
<input type=submit class=button value='Просмотр' name=process>
</form>
{if isset($data)}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr style="font: bold 10pt Arial; align: center:">
<td>&nbsp;</td>
<td>Клиент</td>
<td>Номер заказа с&nbsp;сайта</td>
<td>Организация</td>
<td>Менеджер</td>
<td>Телемаркетинг</td>
<td>Канал продаж</td>
<td>Услуга</td>
<td>Активна&nbsp;С:</td>
<td>Активна&nbsp;По:</td>
<td>Тариф</td>
<td>Цена тарифа</td>
<td>Дата активации</td>
<td>Сумма первого платежа</td>
</tr>

{foreach from=$data item=item key=key name=outer}
	<tr class=even>
	<td>{$smarty.foreach.outer.iteration}</td>
	<td><a href='{$LINK_START}module=newaccounts&action=bill_list&clients_client={$item.client}'>{$item.client}</a></td>
	<td>{$item.site_req_no}</td>
	<td>{$item.organisation}</td>
	<td>{$item.manager}</td>
	<td>{$item.telemark}</td>
	<td>{$item.channel}</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>{$item.sum_rub}<br>({$item.first_pay_data})</td>
				</tr>

	{if count($item.voip) >= 1}
	
	
		{foreach from=$item.voip item=it name=voip}
				<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>VoIP</td>
		
			<td>{$it.actual_from}</td>
			<td>{$it.actual_to}</td>
			<td>{$it.tarif}</td>
			<td>{$it.cost}</td>
			<td>{$it.date_activation}</td>
			</tr>
		{/foreach}
	{/if}

{if (count($item.ip_ports) >= 1)}
		{foreach from=$item.ip_ports item=it name=ip_ports}
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>IP&nbsp;Ports</td>
			<td>{$it.actual_from}</td>
			<td>{$it.actual_to}</td>
			<td>{$it.tarif}</td>
			<td>{$it.cost}</td>
			<td>{$it.date_activation}</td>
				<td></td>
				<td></td>
			</tr>
		{/foreach}
	{/if}
{/foreach}
</TABLE>
{/if}
<script>
optools.DatePickerInit();
</script>