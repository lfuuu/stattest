<table class="table" width="100%" border="1">
<tr>
	<th width="15%">АТС</th>
	<th width="15%">Клиент</th>
	<th width="70%">Даты</th>
</tr>
{if $data}
	{foreach from=$data item="s" key="k"}
		{if $s.ts}
			<tr>
				<td style="text-align: center;">АТС {$k}</td>
				<td style="text-align: center;">{$s.client}</td>
				<td style="text-align: center;">
					{foreach from=$s.ts item="d" name="days"}
						{$d|mdate:"j месяца Y"}
						{if !$smarty.foreach.days.last}
							, 
						{/if}
					{/foreach}
				</td>
			</tr>
		{/if}
	{/foreach}
{else}
	<tr><td colspan="2">Нет данных</td></tr>
{/if}
</table>