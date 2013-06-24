<H2>Рассылки</H2>
<H3>Список писем</H3>
<a href='{$LINK_START}module=letters&action=lview'>Добавить письмо</a><br>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="30%">Тема</TD>
  <TD class=header vAlign=bottom width="50%">Текст</TD>
  <TD class=header vAlign=bottom width="10%">Отослано</TD>
  <TD class=header vAlign=bottom width="10%">&nbsp;</TD>
  </TR>
{foreach from=$letters_letters item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD>{if $item.cnt_files>0}<img src='{$IMAGES_PATH}clip.gif'>{/if}<a href='{$LINK_START}module=letters&action=lview&letter={$item.id}'>{$item.subject}</a></TD>
	<TD style='font-size:85%'>{$item.body}</TD>
	<TD>{$item.cnt_sent} / {$item.cnt_total}</TD>
	<TD><a href='{$LINK_START}module=letters&action=ldelete&letter={$item.id}'>Удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>

<H3>Файлы</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="55%">Имя файла</TD>
  <TD class=header vAlign=bottom width="35%">Размер</TD>
  <TD class=header valign=bottom>&nbsp;</td>
  </TR>
{foreach from=$letters_files item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD>{$item.0}</TD>
	<TD>{fsizeKB value=$item.1}</TD>
	<TD><a href='{$LINK_START}module=letters&action=fdelete&filename={$item.0}'>Удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
<FORM action="?" method=post enctype="multipart/form-data">
<input type=hidden name=module value=letters>
<input type=hidden name=action value=fupload>
<input type=file name=file><INPUT id=submit class=button type=submit value="Добавить файл"></FORM><br>
