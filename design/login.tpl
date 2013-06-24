<!-- ######## Content ######## -->
      <H2>Operator login</H2>
      <H3>Введите логин и пароль:</H3>
      <form action='index.php' method=post>
      <input type=hidden name=action value='login'>
      <div align=center style="padding-right:200px;">
      <TABLE class=header cellSpacing=4 cellPadding=2 width="350" border=0 style="border: 1px solid #E0E0E0; background-color: #F7F7F7;padding-top:10px;padding-left:15px;">
        <TBODY>
        <TR>
          <TD vAlign=middle width="30%">Логин:</TD>
          <TD vAlign=middle width="70%"><input name='login' id="login"></TD>
        </TR>
        <TR>
          <TD vAlign=middle width="30%">Пароль:</TD>
          <TD vAlign=middle width="70%"><input type=password name='password'></TD>
        </TR>
        <TR>
          <TD class=header vAlign=middle width="30%">&nbsp;</TD>
          <TD class=header vAlign=middle align=right width="70%"><input type=submit value='Войти' style="margin-right: 35px;"></TD>
        </TR>
	</TBODY></TABLE>
    </div>
      </FORM><!-- ######## /Content ######## -->
<br>
<script>
document.getElementById("login").focus();
</script>
      
