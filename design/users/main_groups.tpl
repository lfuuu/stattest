<H2>Группы</H2>
<H3>Список групп</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="45%">Группа</TD>
  <TD class=header vAlign=bottom width="45%">Комментарий</TD>
  <TD class=header vAlign=bottom width="10%">&nbsp;</TD>
  </TR>
{foreach from=$groups item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
{if access('users','change')}
	<TD><a href='{$LINK_START}module=users&m=group&id={$item.usergroup}'>{$item.usergroup}</a></TD>
{else}
	<TD>{$item.usergroup}</TD>
{/if}
	<TD>{$item.comment}</TD>
	<TD><a href='{$LINK_START}module=users&m=groups&action=delete&id={$item.usergroup}'>Удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
{if access('users','change')}
      <DIV style="WIDTH: 1px; HEIGHT: 30px"><IMG height=30 alt="" 
      src="<?=IMAGES_PATH;?>1.gif" width=1></DIV>
      <H3>Добавить группу:</H3>
<FORM action="?" method=get>
<input type=hidden name=action value=add>
<input type=hidden name=module value=users>
<input type=hidden name=m value=group>

      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TR><TD class=left>Имя группы:</TD>
          <TD><input name=usergroup class=text></TD>
          </TR>
          <TR><TD class=left>Описание:</TD>
          <TD><input name=comment class=text></TD>
          </TR>
          </TBODY></TABLE>
      <HR>

<DIV align=center><INPUT class=button type=submit value="Создать группу"></DIV>
</FORM>
{/if}