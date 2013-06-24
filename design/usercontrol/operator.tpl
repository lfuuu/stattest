<H2>О пользователе</H2>
<H3>Информация о пользователе {$authuser.user}</H3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0><TBODY>
<TR><TD class=left width=30%>Имя пользователя:</TD><TD><input style='width:100%' class=text value='{$authuser.user}'></TD></TR>
<TR><TD class=left>Группа:</TD><TD><input style='width:100%' class=text value='{$authuser.usergroup}'></TD></TR>
<TR><TD class=left>Полное имя:</TD><TD><input style='width:100%' class=text value='{$authuser.name}'></TD></TR>
<TR><TD class=left>E-mail:</TD><TD><input style='width:100%' class=text value='{$authuser.email}'></TD></TR>
<TR><TD class=left>Телефон рабочий:</TD><TD><input style='width:100%' class=text value='{$authuser.phone_work}'></TD></TR>
<TR><TD class=left>Телефон мобильный:</TD><TD><input style='width:100%' class=text value='{$authuser.phone_mobile}'></TD></TR>
<TR><TD class=left>ICQ:</TD><TD><input style='width:100%' class=text value='{$authuser.icq}'></TD></TR>
{if $authuser.photo}
<TR><TD class=left>Фото:</TD><TD>
<img src='images/users/{$authuser.id}.{$authuser.photo}'>
</TD></TR>
{/if}

</TBODY></TABLE>