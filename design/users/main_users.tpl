<H2>Операторы</H2>
<H3>Список операторов</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="20%">Login</TD>
  <TD class=header vAlign=bottom width="15%">Группа</TD>
  <TD class=header vAlign=bottom width="15%">Отдел</TD>
  <TD class=header vAlign=bottom width="38%">Полное имя</TD>
  <TD class=header vAlign=bottom width="10%">Редирект</TD>
  <TD class=header vAlign=bottom width="2%">Фото?</TD>
  <TD class=header vAlign=bottom width="10%">&nbsp;</TD>
  </TR>
{foreach from=$users item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}{if $item.enabled=='no'} style="text-decoration: line-through"{/if}>
	<TD><a href='{$LINK_START}module=users&m=user&id={$item.user}'>{$item.user}</a></TD>
	<TD><a href='{$LINK_START}module=users&m=group&id={$item.usergroup}'>{$item.usergroup}</a></TD>
	<TD>{$item.depart_name}</TD>
	<TD>{$item.name}</TD>
	<TD>{$item.trouble_redirect}</TD>
	<TD>{if $item.photo}+{/if}</TD>
	<TD><a href='{$LINK_START}module=users&m=users&action=delete&id={$item.user}'>Удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
{if access('users','change')}
      <DIV style="WIDTH: 1px; HEIGHT: 30px"><IMG height=30 alt="" 
      src="<?=IMAGES_PATH;?>1.gif" width=1></DIV>
      <H3>Добавить оператора:</H3>
<FORM action="?" method=get>
<input type=hidden name=action value=add>
<input type=hidden name=module value=users>
<input type=hidden name=m value=user>

      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TR><TD class=left>Логин:</TD>
          <TD><input name=user class=text></TD>
          </TR>
          
          <TR><TD class=left>Группа:</TD>
          <TD><SELECT name=usergroup>{foreach from=$groups item=item}<option value={$item.usergroup}>{$item.usergroup} - {$item.comment}</option>{/foreach}</select></TD>
          </TR>

          <TR><TD class=left>Полное имя:</TD>
          <TD><input name=name class=text></TD>
          </TR>
          </TBODY></TABLE>
      <HR>

<DIV align=center><INPUT class=button type=submit value="Создать оператора"></DIV>
</FORM>
{/if}
