<H2>О пользователе</H2>
<H3>Изменение пользователя {$authuser.user}</H3>
<form action='?' method=post enctype="multipart/form-data">
<input type=hidden name=action value=apply>
<input type=hidden name=module value=usercontrol>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0><TBODY>
<TR><TD class=left width=30%>Полное имя:</TD><TD><input style='width:100%' name=name class=text value='{$authuser.name}'></TD></TR>
<TR><TD class=left>e-mail:</TD><TD><input style='width:60%' name=email class=text value='{$authuser.email}'></TD></TR>
<TR><TD class=left>Внутренний номер (логин в comcenter):</TD><TD><input style='width:60%' name=phone_work class=text value='{$authuser.phone_work}'></TD></TR>
<TR><TD class=left>Телефон мобильный:</TD><TD><input style='width:60%' name=phone_mobile class=text value='{$authuser.phone_mobile}'></TD></TR>
<TR><TD class=left>ICQ:</TD><TD><input style='width:60%' name=icq class=text value='{$authuser.icq}'></TD></TR>
<TR><TD class=left>Показывать заявки на каждой странице:</TD><TD><input name=show_troubles_on_every_page type="checkbox" value='1' {if $authuser.show_troubles_on_every_page}checked{/if}></TD></TR>
<TR><TD class=left>Фотография:</TD><TD><input style='width:60%' name=photo class=text type=file value='' onchange='javscript:photo_change.checked=true;'><input id=file_change value=1 class=text type=checkbox name=photo_change>
{if $authuser.photo}
	<br><img src='images/users/{$authuser.id}.{$authuser.photo}'>
{/if}
	</TD></TR>
</TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
