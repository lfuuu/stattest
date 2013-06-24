<form action="" method="get">
	<input type="hidden" name="module" value="stats" />
	<input type="hidden" name="action" value="report_sms_gate" />
<table>
	<tr><td>Фильтры</td></tr>
	<tr>
		<td>Дата :</td>
		<td><select name="_sms_fil_from_y" onchange="optools.friendly.dates.check_mon_right_days_count('_sms_fil_from_y','_sms_fil_from_m','_sms_fil_from_d')">
			{generate_sequence_options_select start='2003' selected=$_sms_fil_from_y}
		</select></td>
		<td><select name="_sms_fil_from_m" onchange="optools.friendly.dates.check_mon_right_days_count('_sms_fil_from_y','_sms_fil_from_m','_sms_fil_from_d')">
			{generate_sequence_options_select start='1' end='12' selected=$_sms_fil_from_m}
		</select></td>
		<td><select name="_sms_fil_from_d" onchange="optools.friendly.dates.check_mon_right_days_count('_sms_fil_from_y','_sms_fil_from_m','_sms_fil_from_d')">
			{generate_sequence_options_select start='1' end='31' selected=$_sms_fil_from_d}
		</select></td>

		<td>по</td>

		<td><select name="_sms_fil_for_y" onchange="optools.friendly.dates.check_mon_right_days_count('_sms_fil_for_y','_sms_fil_for_m','_sms_fil_for_d')">
			{generate_sequence_options_select start='2003' selected=$_sms_fil_for_y}
		</select></td>
		<td><select name="_sms_fil_for_m" onchange="optools.friendly.dates.check_mon_right_days_count('_sms_fil_for_y','_sms_fil_for_m','_sms_fil_for_d')">
			{generate_sequence_options_select start='1' end='12' selected=$_sms_fil_for_m}
		</select></td>
		<td><select name="_sms_fil_for_d" onchange="optools.friendly.dates.check_mon_right_days_count('_sms_fil_for_y','_sms_fil_for_m','_sms_fil_for_d')">
			{generate_sequence_options_select start='1' end='31' selected=$_sms_fil_for_d}
		</select></td>
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