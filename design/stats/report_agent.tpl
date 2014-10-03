
<style>
{literal}
.tbl_legend {
	font-size: 12px;
	font-family: "Geneva CY", Tahoma, Arial, sans-serif;
	padding-top:15px;
	padding-bottom:10px;
}

{/literal}
</style>

<H2>Отчет по Агентам</H2>

<form action="./?module=stats&action=report_agent" method=post>

	<table border=0 cellpadding=3>
		<tr>
			<td>Агент</td>
			<td>
				{if count($agents) > 0}
					<select name='agent'>
						<option value=''> Выберите агента</option>
						{foreach from=$agents item=item}
							<option value='{$item.id}' {if isset($agent) && $agent.id == $item.id}selected='selected'{/if}> {$item.name}</option>
						{/foreach}
					</select>
					{if $default_interest}
						<span style="color: red;">Поощрения агента не заданы</span>
					{/if}
				{/if}
			</td>
		</tr>
		<tr>
			<td>Расчетный период</td>
			<td><select name='from_m'>{foreach from=$mm item='m' key='key'}<option value='{$key}' {if $key == $cur_m}selected='selected'{/if}>{$m}</option>{/foreach}</select>
			<select name='from_y'>{foreach from=$yy item='y'}<option value='{$y}' {if $y == $cur_y}selected='selected'{/if}>{$y}</option>{/foreach}</select></td>
			
		</tr>
		
		<tr>
			<td colspan="2">
				<input type=submit value="Найти" />
			</td>
		</tr>
        </table>

{if count($inns)}
<input type=submit value="Экспорт в CSV" name="export" />
<div class='tbl_legend'>
	Агент: <b>{$agent.name}</b><br />
	Расчетный период с <b>{$from}</b> г. по <b>{$to}</b> г.
</div>
<div border="1">

    <TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
        <TBODY>
        <TR>
            {assign var="rowspan" value="2"}
            
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom rowspan="{$rowspan}">Компания</TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom rowspan="{$rowspan}">Абон плата, с учетом НДС</TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom rowspan="{$rowspan}">оплаченный период (мес.)</TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom rowspan="{$rowspan}">Сумма полученных платежей</TD>

            <TD style='background-color:#FFFFD8' class=header vAlign=bottom colspan="2">
		Вознаграждение
            </TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom rowspan="{$rowspan}">Сумма вознаграждения</TD>
        </TR>
        
	<tr>
		<td style='background-color:#FFFFD8' class=header vAlign=bottom >
			Тип
		</td>
		<td style='background-color:#FFFFD8' class=header vAlign=bottom >
			%
		</td>
	</tr>
        
        {foreach from=$inns item=item}
		{foreach from=$interests item="i"}
			<TR>
				<td>{$item.company}</td>
				<td align='right'>{$item.isum|number_format:"2":",":" "}</td>
				<td align='right'>{$item.period}</td>
				<td align='right'>{$item.psum|number_format:"2":",":" "}</td>
				<td align='right'>
					{$interests_types.$i.name}
				</td>
				
				<td align='right'>
					{$agent_interests.$i|number_format:"2":",":" "}%
				</td>
					
				{assign var="key" value="`$interest_type`_`$i`"}
				<td align='right'>{$item.fsums.$key|number_format:"2":",":" "}</td>
			</tr>
		{/foreach}
        {/foreach}
        <tr style='font-size: 12px; font-weight: bold'>
            <td colspan='3'>Итого</td>
            <td align='right'>{$total.psum|string_format:"%.2f"}</td>
            <td align='right' colspan="2"></td>
            <td align='right'>{$total.fsum|string_format:"%.2f"}</td>
        </tr>
        </tbody>
    </table>
</div>
<div class='tbl_legend'>
	Вознаграждение агента в Расчетном периоде составляет {$total.fsum_str} в том числе НДС (18%) {$total.nds_str}<br />
	Прописью: {$total.fsum|wordify:'RUR'}
</div>
{/if}
</form>