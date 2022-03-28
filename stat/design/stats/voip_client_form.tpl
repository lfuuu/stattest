      <H3>Создайте отчёт сами: (или - посмотрите отчёты за <a href="?module=stats&action=voip&phone={$phone}&paidonly={$paidonly}&detality={$detality}&date_from={$prev_date_from}&date_to={$prev_date_to}&tariff_id={$tariff_id}">прошлый месяц</a>,
      								за <a href="?module=stats&action=voip&phone={$phone}&paidonly={$paidonly}&detality={$detality}&date_from={$cur_date_from}&date_to={$cur_date_to}&tariff_id={$tariff_id}">текущий месяц</a>)</H3>
      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>

        <TBODY>
        <TR>
          <TD class=left>Телефон:</TD>
          <TD>
            <FORM action="?" method=get>
            <input type=hidden name=module value=stats>
            <input type=hidden name=action value=voip>
            <div style="width:350px;">
            	<SELECT name=phone class="select2">
                	{foreach from=$phones key=key item=item}<option value='{$key}'{if $phone==$key} selected{/if}>{$item}</option>{/foreach}
            	</SELECT>
            </div>
        <TR>
          <TD class=left>Дата начала отчёта</TD>
          <TD>
		<input class="datepicker-input" type=text name="date_from" value="{$date_from}" id="date_from">
		По:<input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
                  <select name=timezone>
                      {foreach from=$timezones item=item}
                          <option value='{$item}'{if $item==$timezone} selected{/if}>{$item}</option>
                      {/foreach}
                  </select>
          </TD>
          </TR>
          <TR>
          <TD class=left>Выводить по:</TD>
          <TD>
		<SELECT name=detality>
			<OPTION value=call{if $detality=='call'} selected{/if}>звонкам</OPTION>
			<OPTION value=day{if $detality=='day'} selected{/if}>дням</OPTION>
			<OPTION value=month{if $detality=='month'} selected{/if}>месяцам</OPTION>
			<OPTION value=year{if $detality=='year'} selected{/if}>годам</OPTION>
			<OPTION value=dest{if $detality=='dest'} selected{/if}>направлениям</OPTION>
			<OPTION value=package{if $detality=='package'} selected{/if}>пакетам</OPTION>
			<OPTION value=filterb{if $detality=='filterb'} selected{/if}>направлениям v2<TD></TR>
		<tr>
			<td class="left" style="text-decoration: line-through">Направление</td>
			<td>
				<select name="destination">
					<option value="all"{if $destination eq 'all'} selected='selected'{/if}>Все</option>
					<option value="0"{if $destination eq '0'} selected='selected'{/if}>Все местные вызовы</option>
					<option value="0-m"{if $destination eq '0-m'} selected='selected'{/if}>&nbsp;&nbsp;Местные мобильные</option>
					<option value="0-f"{if $destination eq '0-f'} selected='selected'{/if}>&nbsp;&nbsp;Местные стационарные</option>
					<option value="0-f-z"{if $destination eq '0-f-z'} selected='selected'{/if}>&nbsp;&nbsp;Зона - стационарные</option>
					<option value="1"{if $destination eq '1'} selected='selected'{/if}>Россия</option>
					<option value="1-m"{if $destination eq '1-m'} selected='selected'{/if}>&nbsp;&nbsp;Россия мобильные</option>
					<option value="1-f"{if $destination eq '1-f'} selected='selected'{/if}>&nbsp;&nbsp;Россия стационарные</option>
					<option value="2"{if $destination eq '2'} selected='selected'{/if}>Международные</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="left">Тариф</td>
			<td>
				<select name="tariff_id">
					<option value=""{if $tariff_id eq ''} selected='selected'{/if}>Все</option>
					{html_options options=$tariffs selected=$tariff_id}
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
		<TR><TD class=left>Только платные звонки:</TD><TD>
		<input type=checkbox name=paidonly value='1'{if $paidonly==1} checked{/if}>
        </TD></TR></TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
<script>
optools.DatePickerInit();
</script>