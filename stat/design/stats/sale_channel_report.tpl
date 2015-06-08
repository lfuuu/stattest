<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="js/ui/i18n/jquery.ui.datepicker-ru.js"></script>
<link href="css/themes/base/jquery.ui.all.css" rel="stylesheet" type="text/css"/>
<style>
{literal}
#date_from, #date_to {
width: 100px;
}

{/literal}
</style>

{if isset($print) && $print}<center><h3>Задания на выезд</center></h3>{/if}
{if isset($print_report)}<a href="{$print_report}" target="_blank">Печать</a>{/if}
{if count($report_data)>0}<table id="doers_report_pane" border="1" cellpadding="3" cellspacing="0" width="100%">
<tr align='center'>
	<th width='10%'>Дата</td>
	<th width='20%'>Ф.И.О.</td>
	<th width='20%'>Организация</td>
	<th width='30%'>Задание</td>
	<th width='20%'>Подпись</td>
</tr>
{foreach from=$report_data item="one_day" key="date"}
	<tr align='center'>
		<td rowspan='{$one_day.rowspan}'>{$date}</td>
		{foreach from=$one_day.doers item="day" key="doer"}
			<td>{$day.name}<br /><br /><b style="color:red">{$tt_states_list[$day.cur_state].name}</b></td>
			<td>{if !isset($print) || !$print}<a href='/client/clientview?id={$day.client_id}' target='_blank' style='text-decoration:none;'>{$day.company}</a><br><br>{if $day.bill_no}<a href="./?module=newaccounts&action=bill_view&bill={$day.bill_no}" target=_blank>{$day.bill_no}</a>{if $view_calc}<br><br>
            счет: {$day.bill_sum} <br>
            товары: {$day.sum_good}{if $day.count_good > 1}(x{$day.count_good}){/if} <br>
            услуги: {$day.sum_service}{if $day.count_service > 1}(x{$day.count_service}){/if}{/if}
        {/if}{else}{$day.company}{/if}</td>
			<td>
				<table width='100%'><tr>
				<td align='center'>{if (!isset($print) || !$print) && $day.tt_id > 0}<a href='?module=tt&action=view&id={$day.tt_id}' target='_blank' style='text-decoration:none;'>{/if}
				{$day.task}{if $day.type eq 'USD'}${/if}
				{if (!isset($print) || !$print) && $day.tt_id > 0}</a>{/if}</td>
				</tr></table>
				{if (!isset($print) || !$print) && $day.tt_id > 0}<a href='?module=tt&action=view&id={$day.tt_id}' target='_blank' style='text-decoration:none;'>{/if}<br />{if $day.trouble_cur_state>0}<b style="color:red">{$tt_states_list[$day.trouble_cur_state].name}</b>{if (!isset($print) || !$print) && $day.tt_id > 0}</a>{/if}{/if}
			</td>
			<td>{if $view_calc}{$day.bonus}{else}&nbsp;{/if}</td>
	</tr>
	<tr align='center'>
		{/foreach}
	</tr>
{/foreach}
{if $view_calc}<tr><td colspan=4></td><td>{$sum_bonus}</td></tr>{/if}
</table>
{/if}
{if !isset($print) || !$print}
<br> Всего заявок: {$count}
<form method="POST">
	<table border='0' align='center'>
		<tr align='center'>
			<td>Начало периода</td>
			<td>Конец периода</td>
			<td>Исполнитель</td>
			<td>Состояние</td>
			<td></td>
		</tr>
		<tr>
			<td><input type=text name="date_from" value="{$date_begin}" id="date_from"></td>
			<td><input type=text name="date_to" value="{$date_end}" id="date_to"></td>
			<td>
                {html_options name='doer_filter' options=$doer_filter selected=$doer_filter_selected}
			</td>
			<td>
				<select name='state_filter'>{foreach from=$l_state_filter item='state'}<option value='{$state.id}'{if $state.id eq $state_filter_selected} selected='selected'{/if}>{$state.name}</option>{/foreach}</select>
			</td>
			<td><input type='submit' value='Отчет' name="do"/></td>
		</tr>
	</table>
</form>{/if}
<script>
{literal}
$( "#date_from" ).datepicker({
    dateFormat: 'yy-mm-dd',
    maxDate: $( "#date_to" ).val(),
    onClose: function( selectedDate ) {
      $( "#date_to" ).datepicker( "option", "minDate", selectedDate );
    }
  });
  $( "#date_to" ).datepicker({
    dateFormat: 'yy-mm-dd',
    minDate: $( "#date_from" ).val(),
    onClose: function( selectedDate ) {
      $( "#date_from" ).datepicker( "option", "maxDate", selectedDate );
    }
  });
{/literal}
</script>

