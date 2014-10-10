     
      <H3>Создайте отчёт сами: (или - посмотрите отчёты за <a href="?module=stats&action=ppp&login={$login}&detality=day&date_from={$prev_date_from}&date_to={$prev_date_to}">прошлый месяц</a>,
      								за <a href="?module=stats&action=ppp&login={$login}&detality=day&date_from={$cur_date_from}&date_to={$cur_date_to}">текущий месяц</a>)</H3>
      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
  
        <TBODY>
        <TR>
          <TD class=left>Логин:</TD>
          <TD>
            <FORM action="?" method=get>
            <input type=hidden name=module value=stats>
            <input type=hidden name=action value=ppp>
            <SELECT name=login>
            	<OPTION value='' selected>все</OPTION>
				{foreach from=$logins_all item=item}<option value={$item.id}{if $login==$item.id} selected{/if}>{$item.login}</option>{/foreach}
              </SELECT>
        <TR>
          <TD class=left>C:</TD>
          <TD>
		<input class="datepicker-input" type=text class="" name="date_from" value="{$date_from}" id="date_from">
		По:<input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
          </TD>
          </TR>
          <TR>
          <TD class=left>Выводить по:</TD>
          <TD>
		<SELECT name=detality>
			<OPTION value=sess{if $detality=='sess'} selected{/if}>сессиям</OPTION>
			<OPTION value=day{if $detality=='day'} selected{/if}>дням</OPTION>
			<OPTION value=month{if $detality=='month'} selected{/if}>месяцам</OPTION>
			<OPTION value=year{if $detality=='year'} selected{/if}>годам</OPTION>
			<OPTION value=login{if $detality=='login'} selected{/if}>логинам</OPTION>
		</SELECT>
        </TD></TR></TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
<script>
optools.DatePickerInit();
</script>      
      <!-- ######## /Content ######## -->