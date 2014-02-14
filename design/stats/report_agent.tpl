
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
                    {/if}
				</td>
				<td>Расчетный период</td>
				<td><select name='from_m'>{foreach from=$mm item='m' key='key'}<option value='{$key}' {if $key == $cur_m}selected='selected'{/if}>{$m}</option>{/foreach}</select></td>
				<td><select name='from_y'>{foreach from=$yy item='y'}<option value='{$y}' {if $y == $cur_y}selected='selected'{/if}>{$y}</option>{/foreach}</select></td>
				<td><input type=submit value="Найти" /></td>
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
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Компания</TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Абон плата, с учетом НДС</TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom >оплаченный период (мес.)</TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Сумма полученных платежей</TD>

            <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Вознаграждение</TD>
            <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Сумма вознаграждения</TD>
        </TR>
        {foreach from=$inns item=item}
        <TR>
            <td>{$item.company}</td>
            <td align='right'>{$item.isum|string_format:"%.2f"}</td>
            <td align='right'>{$item.period}</td>
            <td align='right'>{$item.psum|string_format:"%.2f"}</td>
            <td align='right'>{$agent.interest} %</td>
            <td align='right'>{$item.fsum|string_format:"%.2f"}</td>
        </tr>
        {/foreach}
        <tr style='font-size: 12px; font-weight: bold'>
            <td colspan='3'>Итого</td>
            <td align='right'>{$total.psum|string_format:"%.2f"}</td>
            <td align='right'></td>
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