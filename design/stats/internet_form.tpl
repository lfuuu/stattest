<style>
{literal}
#repart_table td {
    padding: 2px 2px 2px 2px;
    font: normal 8pt sans-serif;
}
#tr_head td {
    font-weight: bold;
    text-align: center;
}
.ui-datepicker {
	font-size: 10px;
}
{/literal}
</style>
      <H3>Создайте отчёт сами: (или - посмотрите отчёты за <a href="?module=stats&route={$route}&action=internet&is_coll={$is_collocation}&detality=day&date_from={$prev_date_from}&date_to={$prev_date_to}">прошлый месяц</a>,
      								за <a href="?module=stats&is_coll={$is_collocation}&route={$route}&action=internet&detality=day&date_from={$cur_date_from}&date_to={$cur_date_to}">текущий месяц</a>,
      								за <a href="?module=stats&is_coll={$is_collocation}&route={$route}&action=internet&detality=hour&date_from={$today}&date_to={$today}">текущий день</a>)</H3>
      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
  
        <TBODY>
	<TR>
		<TD class=left>
			IP:<DIV style="WIDTH: 1px; HEIGHT: 10px"><IMG height=10 alt="" src="<?=IMAGES_PATH;?>1.gif" width=1></DIV>
		</TD>
		<TD>
			<FORM action="?" method=get>
			<input type=hidden name=module value=stats>
			<input type=hidden name=action value=internet>
			<input type=hidden name=is_coll value={$is_collocation}>
			<SELECT name=route>
			<OPTION value='' selected>все</OPTION>
					{foreach from=$routes_all item=item}<option value="{$item[0]}"{if $route==$item[0]} selected{/if}>{$item[0]} ({$item[1]} - {$item[2]})</option>{/foreach}
			</SELECT><BR>
			<DIV style="WIDTH: 1px; HEIGHT: 10px"><IMG height=10 alt="" 
			src="<?=IMAGES_PATH;?>1.gif" width=1></DIV>
		</TD>
		<td colspan="2"></td>
	</TR>
	<tr>
		<td class=left>С:</td>
		<td>
			<input style="width: 12em;" type=text class="" name="date_from" value="{$date_from}" id="date_from">
		</td>
	
		<td class=left>по:</td>
		<td>
			<input style="width: 12em;" type=text name="date_to" value="{$date_to}" id="date_to">
		</td>
	</tr>
        <TR>
          <TD class=left>Выводить по:</TD>
          <TD>
		<SELECT name=detality>
			<OPTION value=ip{if $detality=='ip'} selected{/if}>IP-адресам</OPTION>
			<OPTION value=hour{if $detality=='hour'} selected{/if}>часам</OPTION>
			<OPTION value=day{if $detality=='day'} selected{/if}>дням</OPTION>
			<OPTION value=month{if $detality=='month'} selected{/if}>месяцам</OPTION>
			<OPTION value=year{if $detality=='year'} selected{/if}>годам</OPTION>
		</SELECT>
        </TD>
        <td colspan="2"></td>
        </TR></TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
<script>
{literal}
$( "#date_from" ).datepicker({
    dateFormat: 'dd-mm-yy',
    maxDate: $( "#date_to" ).val(),
    onClose: function( selectedDate ) {
      $( "#date_to" ).datepicker( "option", "minDate", selectedDate );
    }
  });
  $( "#date_to" ).datepicker({
    dateFormat: 'dd-mm-yy',
    minDate: $( "#date_from" ).val(),
    onClose: function( selectedDate ) {
      $( "#date_from" ).datepicker( "option", "maxDate", selectedDate );
    }
  });
{/literal}
</script>