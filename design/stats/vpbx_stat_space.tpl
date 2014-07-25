<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr>
	<td style="text-align: center;" class="header" vAlign=bottom rowspan="2">АТС</td>
	{if !$fixclient}
		<td style="text-align: center;" class="header" vAlign=bottom rowspan="2">Клиент</td>
	{/if}
	<td style="text-align: center;" class="header" vAlign=bottom rowspan="2">Работатет с</td>
	<td style="text-align: center;" class="header" vAlign=bottom rowspan="2">Тариф</td>
	<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Максимум за период</td>
	<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Минимум за период</td>
	<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Среднее за период</td>
	<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Суммарный рост</td>
	<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Суммарное падение</td>
</tr>
<tr>
	<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
	<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
</tr>
{if $stats}
	{foreach from=$stats item="s" name=outer key="k"}
			<tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
				<td style="text-align: center;">
					<a href="?module=stats&action=report_vpbx_stat_space&vpbx={$s->usage_id}&date_from={$date_from}&date_to={$date_to}">АТС {$s->usage_id}</a>
				</td>
				{if !$fixclient}
					<td style="text-align: center;">
						<a href="?module=clients&id={$s->client}">{$s->client}</a>
					</td>
				{/if}
				<td style="text-align: center;">{$s->actual|mdate:"j месяца Y"}</td>
				<td style="text-align: center;">{$s->tarif}</td>
				<td style="text-align: center;">{$s->max|bytesize}</td>
				<td style="text-align: center;">{$s->max_number}</td>
				<td style="text-align: center;">{$s->min|bytesize}</td>
				<td style="text-align: center;">{$s->min_number}</td>
				<td style="text-align: center;">{$s->avg|bytesize}</td>
				<td style="text-align: center;">{$s->avg_number|number_format:"2":",":" "}</td>
				<td style="text-align: center;" class="profit">{$s->profit|bytesize}</td>
				<td style="text-align: center;" class="profit">{$s->profit_number}</td>
				<td style="text-align: center;" class="deficit">{$s->deficit|bytesize}</td>
				<td style="text-align: center;" class="deficit">{$s->deficit_number}</td>
			</tr>
	{/foreach}
{else}
	<tr><td colspan="7" style="text-align: center;">Нет информации</td></tr>
{/if}
</table>
{if $stat_detailed}
	<TABLE class=price cellSpacing=4 cellPadding=2 width="70%" border=0>
		<tr>
			<td style="text-align: center;" class="header" vAlign=bottom rowspan="2">Дата</td>
			<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Показатель</td>
			<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Рост</td>
			<td style="text-align: center;" class="header" vAlign=bottom colspan="2">Падение</td>
		</tr>
		<tr>
			<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
			<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
			<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
			<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
			<td style="text-align: center;" class="header" vAlign=bottom>Дисковое<br/>пространство</td>
			<td style="text-align: center;" class="header" vAlign=bottom>Внутренние<br/>номера</td>
		</tr>
		{foreach from=$stat_detailed item="data" name=outer}
			<tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
				<td style="text-align: center;">{$data->mdate|mdate:"j месяца Y"}</td>
				<td style="text-align: center;">{$data->use_space|bytesize}</td>
				<td style="text-align: center;">{$data->numbers}</td>
				<td style="text-align: center;" class="profit">{$data->profit|bytesize}</td>
				<td style="text-align: center;" class="profit">{$data->profit_number}</td>
				<td style="text-align: center;" class="deficit">{$data->deficit|bytesize}</td>
				<td style="text-align: center;" class="deficit">{$data->deficit_number}</td>
			</tr>
		{/foreach}
	</table>
{/if}