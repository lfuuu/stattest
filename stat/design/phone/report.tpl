<H2>Виртуальная АТС</H2>
<H3>Отчёт о пропущенных вызовах</H3>
<FORM action="?" method=get id=form name=form>
<input type=hidden name=action value=report_save>	
<input type=hidden name=module value=phone>
<table><tr><td>
	<TABLE class=mform cellSpacing=4 cellPadding=2 border=0><TBODY>
<TR><TD class=left>E-mail:</td><td><input class=text name=email value='{$phone_report.email}'></td></tr>
<tr><td class=left>Как часто присылать отчёты:</td><td><select class=text name=period>
<option{if $phone_report.period=='0'} selected{/if} value='0'>Немедленно</option>
<option{if $phone_report.period=='5m'} selected{/if} value='5m'>Не чаще, чем 1 раз в 5 минут</option>
<option{if $phone_report.period=='30m'} selected{/if} value='30m'>Не чаще, чам 1 раз в 30 минут</option>
<option{if $phone_report.period=='1h'} selected{/if} value='1h'>Не чаще, чам раз в час</option>
<option{if $phone_report.period=='6h'} selected{/if} value='6h'>Не чаще, чам 1 раз за 6 часов</option>
<option{if $phone_report.period=='1d'} selected{/if} value='1d'>Не чаще, чам 1 раз за день</option>
</select></tr>
</tbody></table>
<INPUT id=submit class=button type=submit value="Изменить"><br>
</form>
