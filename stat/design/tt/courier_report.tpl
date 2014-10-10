<h2>Загрузка курьеров по доставке по счетам</h2>
{if count($data)>0}<table id="doers_report_pane" border="1" cellpadding="3" cellspacing="0" width="100%; border-collapse: collapse;">
<tr align='center'>
	<th width='30%' rowspan=2>Отдел</td>
	<th width='40%' rowspan=2>Исполнитель</td>
	<th width='30%' colspan=3>Количество</td>
</tr>
<tr align='center'>
	<th width='10%'>Не WiMax</td>
	<th width='10%'>WiMax</td>
	<th width='10%'>Всего</td>
</tr>
{assign var="count_all" value=0}
{assign var="count_self" value=0}
{assign var="count_wm" value=0}
{foreach from=$data item="i" }
	<tr{if $i.doer_id == 103} style="background-color: #c0f4c0;text-align: center; font-weight: bold;"{/if}>
        {if $i.doer_id == 103}
                {assign var="count_self" value=$i.count}
                <td colspan=2>{$i.name}</td>
        {else}
                <td>{$i.depart}</td>
                <td>{$i.name}</td>
        {/if}
        {assign var="count_all" value="`$count_all+$i.count`"}
        {assign var="count_wm" value="`$count_wm+$i.wm_count`"}
        <td style="text-align: left;">{$i.count-$i.wm_count}</td>
        <td style="text-align: left;">{$i.wm_count}</td>
        <td style="text-align: left;">{$i.count}</td>
	</tr>
{/foreach}
</table>
{/if}

Всего: <b>{$count_all}</b><br>
Самовывоз: <b>{$count_self}</b><br>
WiMax: <b>{$count_wm}</b>

{if !isset($print) || !$print}
<form method="POST">
	<table border='0' align='center'>
	<tr align='center'><td colspan='2'>Период</td></tr>
	<tr align='center'><td>Год</td><td>Месяц</td></tr>
	<tr>
	<td> <select name='date_y'> {generate_sequence_options_select start='2003' selected=$date_y} </select>
	</td><td> <select name='date_m'> {generate_sequence_options_select start='1' end='12' mode='m' selected=$date_m} </select>
	</td>
	</tr>
	<tr align='center'><td colspan='6'><input type='submit' value='Отчет' name="do"/></td></tr>
	</table>
</form>{/if}
