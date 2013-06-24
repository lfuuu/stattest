      <H2>Смена пароля на PPP-логин</H2>
      <H3>{$ppp.login}</H3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<FORM action="?" method=post>
<input type=hidden name=action value=ppp_chreal>
<input type=hidden name=module value=services>
<input type=hidden name=id value={$ppp.id}>
<TR>
	<TD class=left>Пароль:</TD>
	<TD><input name=pass1 class=text type=password></td>
</TR>
<TR>
	<TD class=left>Пароль (ещё раз):</TD>
	<TD><input name=pass2 class=text type=password></td>
</TR>
<tr>
	<td class=left>&nbsp;</td>
	<td><INPUT class=button type=submit value="Установить пароль"></td>
</FORM>
</TBODY></TABLE>
