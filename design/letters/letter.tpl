{if $refresh}
<script>
setTimeout('window.location.reload()',{$refresh*1000});
</script>
{/if}
<H2>Рассылки</H2>
<H3>Редактирование письма</H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=module value=letters>
<input type=hidden name=action value=lapply>
<input type=hidden name=letter value='{$letter.id}'>
<input type=text name=subject style='width:80%' value='{$letter.subject}'><br>
<textarea name=body style='width:80%;height:250px'>{$letter.body}</textarea><br>
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV></FORM>
{if $letter.id}
<H3>Файлы</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="55%">Имя файла</TD>
  <TD class=header vAlign=bottom width="35%">Размер</TD>
  <TD class=header valign=bottom>&nbsp;</td>
  </TR>
{foreach from=$letter_files item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD>{$item.0}</TD>
	<TD>{fsizeKB value=$item.1}</TD>
	<TD><a href='{$LINK_START}module=letters&action=funassign&letter={$letter.id}&filename={$item.0}'>Удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
<FORM action="?" method=post style='padding-bottom:0;margin-bottom:0;'>
<input type=hidden name=module value=letters>
<input type=hidden name=action value=fassign>
<input type=hidden name=letter value='{$letter.id}'>
<select name=filename>
{foreach from=$letters_files item=item name=outer}<option value="{$item.0}">{$item.0} ({fsizeKB value=$item.1})</option>{/foreach}
</select><INPUT id=submit class=button type=submit value="Добавить файл"></FORM>
<FORM action="?" method=post enctype="multipart/form-data" style='padding-top:0;margin-top:5;'>
<input type=hidden name=module value=letters>
<input type=hidden name=action value=fupload2>
<input type=hidden name=letter value='{$letter.id}'> <input type=file name=file><INPUT id=submit class=button type=submit value="Загрузить файл"></FORM><br>


<H3>Состояние</H3>
<a href='{$LINK_START}module=letters&action=process&letter={$letter.id}&test=1'>Тестовая отправка счетов</a> (5 штук)<br>
<a href='{$LINK_START}module=letters&action=process&letter={$letter.id}&test=0'>Реальная отправка счетов</a> (5 штук)<br>
<a href='{$LINK_START}module=letters&action=process&letter={$letter.id}&test=0&cont=1'>Реальная отправка счетов</a> (все)<br>
<a href='{$LINK_START}module=letters&action=unassign&letter={$letter.id}'>Удалить неотосланные письма</a> (error и ready)<br>
<a href='javascript:toggle2(document.getElementById("div_addc"));'>Добавить клиентов</a><br>
<div id='div_addc' style='padding-left:20px; display:none; padding-top:10px;'>
<a href='{$LINK_START}module=letters&action=filter&letter={$letter.id}&filter=internet'>с интернет-каналом</a><br>
<a href='{$LINK_START}module=letters&action=filter&letter={$letter.id}&filter=voip'>с телефоном</a><br>
<a href='{$LINK_START}module=letters&action=filter&letter={$letter.id}&filter=email'>с e-mail ящиками</a><br>
С первыми цифрами номера интернет-канала: <form style='display:inline;padding:0;margin:0' action='?' method=get>
	<input type=hidden name=module value=letters>
	<input type=hidden name=action value=filter>
	<input type=hidden name=letter value='{$letter.id}'>
	<input type=hidden name=filter value=number>
	<input type=text class=text name=filter_param value='123'><input type=submit class=submit value='ok'></form><br>
Использующих роутер: <form style='display:inline;padding:0;margin:0' action='?' method=get>
	<input type=hidden name=module value=letters>
	<input type=hidden name=action value=filter>
	<input type=hidden name=letter value='{$letter.id}'>
	<input type=hidden name=filter value=router>
	<select name=filter_param>
{foreach from=$routers item=item name=outer}
		<option value={$item.router}>{$item.router}</option>
{/foreach}
	</select>
	
		<input type=submit class=submit value='ok'></form><br>
С доп. услугами: <form style='display:inline;padding:0;margin:0' action='?' method=get>
	<input type=hidden name=module value=letters>
	<input type=hidden name=action value=filter>
	<input type=hidden name=letter value='{$letter.id}'>
	<input type=hidden name=filter value=add>

	<select multiple=1 name=filter_add[]>
	{foreach from=$letter_services item=item name=outer}
		<option value="{$item.id}">{$item.description}</option>
	{/foreach}
	</select>
	<input type=submit class=submit value='ok'></form><br>
Назначенных на фирму: <form style='display:inline;padding:0;margin:0' action='?' method=get>
	<input type=hidden name=module value=letters>
	<input type=hidden name=action value=filter>
	<input type=hidden name=letter value='{$letter.id}'>
	<input type=hidden name=filter value=firma>
	<select name=filter_param class=text><option value='mcn'>MCN</option><option value='markomnet'>Маркомнет</option></select>
	<input type=submit class=submit value='ok'></form><br>

</div>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="30%">Клиент</TD>
  <TD class=header vAlign=bottom width="15%">Состояние</TD>
  <TD class=header vAlign=bottom width="15%">Дата отправки</TD>
  <TD class=header valign=bottom>Сообщение об ошибке, если есть</td>
  </TR>
{foreach from=$letter_assigns item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}{if $item.state=='sent' && !isset($item.cur_sent)} style='color:gray'{/if}>
	<TD><a href='{$LINK_START}module=clients&id={$item.client}'>{$item.client}</a></TD>
	<TD{if (isset($item.cur_sent)) && ($item.cur_sent==1)} style='color:red;font-weight:bold'{/if}>{$item.state}</TD>
	<TD>{if $item.last_send!="0000-00-00 00:00:00"}{$item.last_send}{/if}</TD>
	<TD style='font-size:80%'>{$item.message}</TD>
</TR>
{/foreach}
</TBODY></TABLE>
{/if}
