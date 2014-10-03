<TABLE class=mform cellSpacing=4 cellPadding=2 border=0 width=90%>
<TBODY>
<FORM action='?' method=post name=form id=form>
<input type=hidden name=action value=apply>
<input type=hidden name=module value=register>

<TR><TD width=15%>E-mail:</TD><TD><input id=iuser name=user value='{$regform.user}' onchange='javascript:if (form.iname.value=="") form.iname.value=this.value;'></TD></TR>
<TR><TD>Пароль:</TD><TD><input name=pass type=password value=''></TD></TR>
<TR><TD>пароль повторно:</TD><TD><input name=pass2 type=password value=''></TD></TR>
<TR><TD>Полное имя:</TD><TD><input id=iname name=name value='{$regform.name}'></TD></TR>
<TR><TD>&nbsp;</TD><TD><input type=submit value='Зарегистрироваться'></TD></TR>
</FORM>
</TBODY></TABLE>
Ваш e-mail будет использоваться в качестве логина.<br>
После регистрации вы должны получить проверочное письмо. После того, как вы посетите ссылкы из этого письма, регистрацию можно будет считать полностью пройденной.<br>
