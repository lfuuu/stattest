<h2>Загрузка курьеров по доставке по счетам</h2>
{if count($data)>0}<table id="doers_report_pane" border="1" cellpadding="3" cellspacing="0" width="100%; border-collapse: collapse;">
<tr align='center'>
	<th width='30%' rowspan=3>Отдел</td>
	<th width='40%' rowspan=3>Исполнитель</td>
	<th width='30%' colspan={$departs_info.all}>Количество</td>
	<th rowspan=3>Всего</td>
</tr>
<tr align='center'>
    {foreach from=$departs item=dd key=k}
            <th colspan="{$departs_info[$k]}">{$k}</td>
    {/foreach}
</tr>
<tr align='center'>
    {foreach from=$departs item=dd key=k}
        {foreach from=$dd item=d}
            <th>{$d}</td>
        {/foreach}
    {/foreach}
</tr>
{assign var="count_all" value=0}
{assign var="count_self" value=0}
{foreach from=$data item="i" key=doerId }

	<tr{if $doerId == 103} style="background-color: #c0f4c0;text-align: center; font-weight: bold;"{/if}>
        {if $doerId == 103}
                <td colspan=2>{$doers[$doerId].name}</td>
        {else}
                <td>{$doers[$doerId].depart}</td>
                <td>{$doers[$doerId].name}</td>
        {/if}
        {assign var="count_line" value=0}
    {foreach from=$departs item=dd key=k}
        {foreach from=$dd item=d}
            {assign var="count_all" value="`$count_all+$i[$d]`"}
            {assign var="count_line" value="`$count_line+$i[$d]`"}
            {if $doerId == 103}
                    {assign var="count_self" value=`$count_self+$i[$d]`}
            {/if}
            <td style="text-align: left;">&nbsp;{$i[$d]}</td>
        {/foreach}
        {/foreach}
        <td style="text-align: left;">{$count_line}</td>
	</tr>
{/foreach}
<tr>
<td colspan=2 align=right><b>Итого:</b></td>
    {foreach from=$departs item=dd key=k}
        {foreach from=$dd item=d}
            <td><b>{$total[$d]}</b></td>
        {/foreach}
    {/foreach}
        <td><b><u>{$count_all}</u></b></td>
</tr>
</table>
{/if}

Всего: <b>{$count_all}</b><br>
Самовывоз: <b>{$count_self}</b><br>
WiMax: <b>{$total.WiMaxComstar}</b>

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
