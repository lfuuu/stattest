      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
            <FORM action="?" method=get>
            <input type=hidden name=module value=stats>
            <input type=hidden name=action value=report_traff_less>
        <TR>
          <TD class=left>Отчёт за период</TD>
          <TD>

           с: <input class="datepicker-input" type=text class="" name="date_from" value="{$date_from}" id="date_from">
По: <input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
        </TD></TR>
        <TR>
            <TD class=left>Суммарный трафик меньше: </TD>
            <TD><input class=text name=traf_less value="{$traf_less}"> Мб/день</td>
		</TR>
		<TR>
            <TD class=left>Менеджер: </TD>
            <TD>
				<select name='manager'>
					{html_options options=$managers selected=$manager}
				</select>
			</TD>
		</TR>
		<tr><td class=left>Включать клиентов, отключенных в отчетный период:</td><td><input type='checkbox' {if $offclients}checked='checked'{/if} name='offclients' /></td></tr>
        </TBODY></TABLE>
      <HR>

<DIV align=center><INPUT class=button type=submit name=make_report value="Сформировать отчёт"></DIV></FORM>
<script>
optools.DatePickerInit('');
</script>
