<H2>Виртуальная АТС</H2>
{if count($phones_mail)}
<H3>Голосовая почта - файл голосового сообщения</h3>
<form action='?' method=post enctype="multipart/form-data">
<input type=hidden name=action value=mail_file>	
<input type=hidden name=module value=phone>
<table><tr><td>
<TABLE class=mform cellSpacing=4 cellPadding=2 border=0><TBODY>
<TR><TD class=left>Файл в формате wav:</TD><TD><input name=sound class=text type=file value=''><br>{if $phone_mail_file}Текущий файл: {fsizeKB value=$phone_mail_file.size}{/if}
<tr><td class=left>Комментарий:</td><td><input class=text name=comment value='{$phone_mail_file.comment}'></td></tr>
</tbody></table>
<INPUT id=submit class=button type=submit value="Обновить файл"><br>
</FORM>

<H3>Голосовая почта - номера для прослушивания сообщений</h3>
Для прослушивания голосовых сообщений необходимо указать телефон, с которого вы будете их слушать, в правом столбце. После этого вы звоните на наш специальный номер xxx-xxxx и слушаете<br>
<FORM action="?" method="POST">
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<input type=hidden name=action value='mail_save'>
<input type=hidden name=module value=phone>
<TBODY>
<TR><td class=header>Номер в MCN</td><td class=header>С какого номера слушать сообщения</td></tr>
{foreach from=$phones_mail item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==count($phones_mail)%2}even{else}odd{/if}>
	<TD>{$item.E164}</TD>
	<TD><input class=text name=phone_listen[{$item.id}] value="{$item.phone_listen}"></td>
</TR>
{/foreach}
</TBODY></TABLE>
<INPUT class=button type=submit value="Сохранить"><br>
</form>
{else}
<h3>Голосовая почта</h3>
У вас нет активных телефонных номеров. Работа с голосовой почтой невозможна.<br>
{/if}