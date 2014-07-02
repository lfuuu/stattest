<form action="" method="get">
	<input type="hidden" name="module" value="stats" />
	<input type="hidden" name="action" value="report_sms_gate" />
<table>
	<tr><td>Фильтры</td></tr>
	<tr>
		<td>Дата:</td>
		<td>
			c <input class="datepicker-input" type=text class="" name="date_from" value="{$date_from}" id="date_from">
			по <input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
		</td>
	</tr>
	<tr><td>Клиент</td><td colspan="6"><select name="client_fil">{foreach from=$clients item='c'}<option value="{$c.id}"{if $c.current eq 1} selected='selected'{/if}>{$c.client}</option>{/foreach}</select></td></tr>
	<tr><td colspan="7"><input type="submit" value="Ok" /></td></td>
</table>
</form>
<br /><br />
<table align="center">
	<tr style="text-align:center;background-color:#dfdfdf;"><td>Общее количество</td><td>{$stat.total}</td></tr>
	<tr style="text-align:center;background-color:#cfcfcf;"><td>Время</td><td>Количество</td></tr>
	{foreach from=$stat.rows item='s'}
	<tr style="text-align:center;background-color:{cycle values="#dfdfdf,#cfcfcf"};"><td>{$s.date_hour}</td><td>{$s.count}</td></tr>
	{/foreach}
</table>
<script>
optools.DatePickerInit();
</script>