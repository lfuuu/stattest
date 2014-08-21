<H2>Статистика звонков</H2>

<FORM action="?" method=get>
	<input type=hidden name=module value=monitoring>
	<input type=hidden name=action value=report_voip_graph>
	<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
		<TBODY>
			<TR>
				<TD class=left>
					<label for="region">Регион:</label>
				</TD>
				<TD>
					<select name="region" id="region">
						{foreach from=$regions item="r"}
							<option value="{$r.id}" {if $region == $r.id}selected="selected"{/if}>
								{$r.name}
							</option>
						{/foreach}
					</select>
				</TD>
				
			</TR>
		</TBODY>
       </TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV>
</FORM>
<div>
	<img src="{$graph_count}">
</div>
<div>
	<img src="{$graph_duration}">
</div>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr>
	<td class="header" rowspan=2 vAlign=bottom>Неделя (кол-во учтенных дней)</td>
	<td class="header" colspan=2 vAlign=bottom>Характеристики звонков</td>
	<td class="header" colspan=2 vAlign=bottom>Максимум за неделю</td>
	<td class="header" colspan=2 vAlign=bottom>Минимум за неделю</td>
	<td class="header" colspan=2 vAlign=bottom>Среднее за неделю</td>
	<td class="header" colspan=2 vAlign=bottom>Разница с прошлой неделей</td>
</tr>
<tr>
	<td class="header" vAlign=bottom>Кол-во</td>
	<td class="header" vAlign=bottom>Продолжительность</td>
	<td class="header" vAlign=bottom>Кол-во</td>
	<td class="header" vAlign=bottom>Продолжительность</td>
	<td class="header" vAlign=bottom>Кол-во</td>
	<td class="header" vAlign=bottom>Продолжительность</td>
	<td class="header" vAlign=bottom>Кол-во</td>
	<td class="header" vAlign=bottom>Продолжительность</td>
	<td class="header" vAlign=bottom>Кол-во</td>
	<td class="header" vAlign=bottom>Продолжительность</td>
</tr>
{foreach from=$week_stats item="w" key="k" name="week"}
	<tr class={if $smarty.foreach.week.iteration%2==0}even{else}odd{/if}>
		<td>
			{$w.week_start|mdate:"d месяца"} - {$w.week_end|mdate:"d месяца"}{if $w.count_in_week != 7}({$w.count_in_week}){/if} 
		</td>
		<td align=right>
			{$w.count|num_format:true}
		</td>
		<td align=right>
			{$w.len|num_format:true:2}
		</td>
		<td align=right>
			{$w.max|num_format:true}
		</td>
		<td align=right>
			{$w.len_max|num_format:true:2}
		</td>
		<td align=right>
			{$w.min|num_format:true}
		</td>
		<td align=right>
			{$w.len_min|num_format:true:2}
		</td>
		<td align=right>
			{$w.avg|num_format:true}
		</td>
		<td align=right>
			{$w.len_avg|num_format:true:2}
		</td>
		<td align=right>
			<span style="color: {if $w.diff > 0}#000033{elseif $w.diff<0}#663300{else}#C0C0C0{/if}">{$w.diff|num_format:true}</span>
		</td>
		<td align=right>
			<span style="color: {if $w.len_diff > 0}#000033{elseif $w.len_diff<0}#663300{else}#C0C0C0{/if}">{$w.len_diff|num_format:true:2}</span>
		</td>
	</td>
{/foreach}
</table>
{if $no_calls_periods}
	<h2>Даты, когда не было звонков</h2>
	<ul>
		{foreach from=$no_calls_periods item=c}
			<li>
				{foreach from=$c item=d  name="no_call"}
					{if $d.start == $d.end}
						{$d.start|mdate:"d"}
					{else}
						{$d.start|mdate:"d"} - {$d.end|mdate:"d"}
					{/if}
					{if !$smarty.foreach.no_call.last}
						,
					{/if}
				{/foreach}
				{$d.start|mdate:" месяца Y"}
			</li>
		{/foreach}
	</ul>
{/if}



	