<H2>Логи оповeщений{if $fixclient} клиента <span style="font-size:21px;font-weight:bold;color: blue;">{$fixclient}{/if}</H2>
      <H3>Создайте отчёт сами: (или - посмотрите логи за <a href="?module=logs&action=alerts&date_from={$prev_date_from}&date_to={$prev_date_to}">прошлый месяц</a>,
      								за <a href="?module=logs&action=alerts&date_from={$cur_date_from}&date_to={$cur_date_to}">текущий месяц</a>,
      								за <a href="?module=logs&action=alerts&date_from={$today}&date_to={$today}">текущий день</a>)</H3>
<FORM action="?" method=get>

<input type=hidden name=module value=logs>
<input type=hidden name=action value=alerts>

<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
	<TBODY>
		<tr>
			<td class=left>С:</td>
			<td>
				<input class="datepicker-input" type=text class="" name="date_from" value="{$date_from}" id="date_from">
				По:<input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
			</td>
		</tr>
		<TR>
			<TD class=left>
				Событие:
			</TD>
			<TD>
				{foreach from=$events_description item="event" key="key"}
					<div class="form-field">
						<input type="checkbox" id="{$key}" name="events[]" value="{$key}" {if $key|in_array:$events || !$events}checked="checked"{/if}>
						<label for="{$key}">{$event}</label>
					</div>
				{/foreach}
			</TD>

		</TR>
		{if !$fixclient}
			<tr>
				<td class="left">
					Менеджер:
				</td>
				<td>
					<select name='manager'>
						<option value=''>(не фильтровать по этому полю)</option>
						{foreach from=$f_manager item=r}
							<option value='{$r.user}'{if $r.user==$manager} selected="selected"{/if}>{$r.name} ({$r.user})</option>
						{/foreach}
					</select>
				</td>
			</tr>
		{/if}
</TBODY>
</TABLE>
<HR>

	<DIV align=center>
		<INPUT class=button type=submit value="Сформировать отчёт">
	</DIV>
</FORM>
<script>
	optools.DatePickerInit();
</script>