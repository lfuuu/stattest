<FORM action="?" method=get>
<input type='hidden' name='module' value='voip'>
<input type='hidden' name='action' value='stats'>
<table class='mform' cellSpacing='4' cellPadding='2' width="100%" border='0'>
<tbody>
<tr>
	<td class='left'>Телефон:</td>
	<td>
		<select name='phone'>
			<OPTION value=0{if !$phone} selected{/if}>все</OPTION>
			{foreach from=$phones item=item}<option value='{$item}'{if $phone==$item} selected{/if}>{$item}</option>{/foreach}
		</select>
	</td>
<tr>
	<td class='left'>Дата начала отчёта</td>
	<td>
		<select name='from_d' {*onchange="optools.friendly.dates.check_mon_right_days_count('from_y','from_m','from_d')"*}>
		{generate_sequence_options_select start=1 end=31 mode='d' selected=$from_d}
		</select>
		<select name='from_m' {*onchange="optools.friendly.dates.check_mon_right_days_count('from_y','from_m','from_d')"*}>
			{generate_sequence_options_select start=1 end=12 mode='m' selected=$from_m}
		</select>
		<select name='from_y' {*onchange="optools.friendly.dates.check_mon_right_days_count('from_y','from_m','from_d')"*}>
			{generate_sequence_options_select start=2003 selected=$from_y}
		</select>
	</td>
</tr>
<tr>
  <td class='left'>По какую дату</td>
  <td>
<select name='to_d' {*onchange="optools.friendly.dates.check_mon_right_days_count('to_y','to_m','to_d')"*}>
	{generate_sequence_options_select start=1 end=31 mode='d' selected=$to_d}
</select>
<select name='to_m' {*onchange="optools.friendly.dates.check_mon_right_days_count('to_y','to_m','to_d')"*}>
	{generate_sequence_options_select start=1 end=12 mode='m' selected=$to_m}
</select>
<select name='to_y' {*onchange="optools.friendly.dates.check_mon_right_days_count('to_y','to_m','to_d')"*}>
	{generate_sequence_options_select start=2003 selected=$to_y}
</select> </td></tr><tr>
  <td class='left'>Выводить по:</td>
  <td>
<select name='detality'>
	<option value='call'{if $detality=='call'} selected{/if}>звонкам</option>
	<option value='day'{if $detality=='day'} selected{/if}>дням</option>
	<option value='month'{if $detality=='month'} selected{/if}>месяцам</option>
	<option value='year'{if $detality=='year'} selected{/if}>годам</option>
</select>
</td></tr>
<tr>
	<td class="left">Направление</td>
	<td>
		<select name="dgroup">
			<option value="all">Все</option>
			{foreach from=$dgroups item='dg'}
			<option value="{$dg.pk}"{if $dg.pk==$dgroup} selected='selected'{/if}>{$dg.name}</option>
			{/foreach}
		</select>
	</td>
</tr>
<tr>
	<td class="left">Округ</td>
	<td>
		<select name="dsubgroup">
			<option value="all">Все</option>
			{foreach from=$dsubgroups item='dsg'}
			<option value="{$dsg.pk}"{if $dsg.pk==$dsubgroup} selected='selected'{/if}>{$dsg.name}</option>
			{/foreach}
		</select>
	</td>
</tr>
<tr>
	<td class="left">Мобильные/Стационарные</td>
	<td>
		<select name="fixormob">
			<option value="both">Все</option>
			<option value="fix"{if $fixormob=='fix'} selected='selected'{/if}>Стационарные</option>
			<option value="mob"{if $fixormob=='mob'} selected='selected'{/if}>Мобильные</option>
		</select>
	</td>
</tr>
<tr>
	<td class="left">Входящие/Исходящие</td>
	<td>
		<select name="direction">
			<option value="both">Все</option>
			<option value="in"{if $direction eq 'in'} selected='selected'{/if}>Входящие</option>
			<option value="out"{if $direction eq 'out'} selected='selected'{/if}>Исходящие</option>
		</select>
	</td>
</tr>
<tr>
	<td class=left>Платные/Бесплатные звонки:</td>
	<td>
		<select name="is_priced">
			<option value="1"{if $is_priced=='1'} selected='selected'{/if}>Платные</option>
			<option value="-1"{if $is_priced=='-1'} selected='selected'{/if}>Бесплатные</option>
			<option value="0"{if $is_priced=='0'} selected='selected'{/if}>Все</option>
		</select>
	</td>
</tr>
</tbody>
</table>
<HR>

<DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM><!-- ######## /Content ######## -->
<br>Примечание. На тарифных планах, имеющих включенные в абонентскую плату минуты разговора, проверка превышения происходит при выставлении счетов.<br>