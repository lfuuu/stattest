<h2>Выставленные счета за {$ts|mdate:"месяц Y"} года по региону {$region_name}</h2>
<br/>
<table class="price">
	<tr>
		<th colspan=2>
			Клиент
		</th>
		<th>
			Номер счета
		</th>
		<th>
			Сумма
		</th>
		<th>
			Дата
		</th>
	</tr>
	{if $bills}
		{foreach from=$bills item="p" name="outer"}
			<tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
				<td>
					<a href="/client/view?id={$p.client_id}">{$p.client_id}</a>
				</td>
				<td>
					<a href="/client/view?id={$p.client}">{$p.client}</a>
				</td>
				<td>
					<a href="?module=newaccounts&action=bill_view&bill={$p.bill_no}">{$p.bill_no}</a>
				</td>
				<td align="right">
					{$p.sum|number_format:2:",":" "} {$p.currency}
				</td>
				<td>
					{$p.bill_date|mdate:"d месяца Y"}
				</td>
			</tr>
		{/foreach}
		<tr>
			<th colspan=3>
				Всего
			</th>
			<td align="right">
				{$total|number_format:2:",":" "}
			</td>
		</tr>
	{else}
		<tr>
			<td colspan="5">Нет данных</td>
		</tr>
	{/if}
</table>