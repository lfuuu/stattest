<h2>{$title}</h2>
<br/>
<table class="price">
	<tr>
		<th colspan=2>
			Клиент
		</th>
		<th>
			Номер
		</th>
		<th>
			Количество линий
		</th>
		<th>
			Тариф
		</th>
		<th>
			Работает с
		</th>
		<th>
			Работает по
		</th>
		
	</tr>
	{if $phones}
		{foreach from=$phones item="p" name="outer"}
			<tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
				<td>
					<a href="/client/clientview?id={$p.client_id}">{$p.client_id}</a>
				</td>
				<td>
					<a href="/client/clientview?id={$p.client}">{$p.client}</a>
				</td>
				<td>
					{$p.E164}
				</td>
				<td align="right">
					{$p.no_of_lines}
				</td>
				<td>
					{$p.tarif}
				</td>
				<td>
					{$p.actual_from|mdate:"d месяца Y"}
				</td>
				<td>
					{if $p.actual_to == "--"}{$p.actual_to}{else}{$p.actual_to|mdate:"d месяца Y"}{/if}
				</td>
				
			</tr>
		{/foreach}
		<tr>
			<th colspan=2>
				<b>Всего</b>
			</th>
			<td align="right">
				{$totals.num_count}
			</td>
			<td align="right">
				{$totals.count_lines}
			</td>
			<td colspan=2>
				&nbsp;
			</td>
		<tr>
	{else}
		<tr>
			<td colspan="5">Нет данных</td>
		</tr>
	{/if}
</table>