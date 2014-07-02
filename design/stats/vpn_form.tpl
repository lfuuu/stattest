      <H3>Создайте отчёт сами: (или - посмотрите отчёты за <a href="?module=stats&ip={$ip}&action=vpn&detality=day&date_from={$prev_date_from}&date_to={$prev_date_to}">прошлый месяц</a>,
      								за <a href="?module=stats&ip={$ip}&action=vpn&detality=day&date_from={$cur_date_from}&date_to={$cur_date_to}">текущий месяц</a>)</H3>
      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
  
        <TBODY>
        <TR>
          <TD class=left>IP:<BR>
            <DIV style="WIDTH: 1px; HEIGHT: 10px"><IMG height=10 alt="" 
            src="<?=IMAGES_PATH;?>1.gif" width=1></DIV></TD>
          <TD>
            <FORM action="?" method=get>
            <input type=hidden name=module value=stats>
            <input type=hidden name=action value=vpn>
            <SELECT name=ip>
            	<OPTION value='' selected>все</OPTION>
				{foreach from=$IPs item=item}<option value={$item.ip}{if $route==$item.ip} selected{/if}>{$item.ip} ({$item.actual_from} - {$item.actual_to})</option>{/foreach}
              </SELECT><BR>
            <DIV style="WIDTH: 1px; HEIGHT: 10px"><IMG height=10 alt="" 
            src="<?=IMAGES_PATH;?>1.gif" width=1></DIV></TD>
        <TR>
          <TD class=left>Дата начала отчёта</TD>
          <TD>
		<input class="datepicker-input" type=text class="" name="date_from" value="{$date_from}" id="date_from">
		По:<input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
          </TD></TR><TR>
          <TD class=left>Выводить по:</TD>
          <TD>
		<SELECT name=detality>
			<OPTION value=ip{if $detality=='ip'} selected{/if}>IP-адресам</OPTION>
			<OPTION value=hour{if $detality=='hour'} selected{/if}>часам</OPTION>
			<OPTION value=day{if $detality=='day'} selected{/if}>дням</OPTION>
			<OPTION value=month{if $detality=='month'} selected{/if}>месяцам</OPTION>
			<OPTION value=year{if $detality=='year'} selected{/if}>годам</OPTION>
		</SELECT>
        </TD></TR></TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
<script>
optools.DatePickerInit();
</script>