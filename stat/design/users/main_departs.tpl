<H2>Отделы</H2>
<H3>Список отделов</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="90%">Отдел</TD>
  <TD class=header vAlign=bottom width="10%">&nbsp;</TD>
  </TR>
{foreach from=$departs item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
{if access('users','change')}
	<TD><a href='{$LINK_START}module=users&m=departs&id={$item.id}'>{$item.name}</a></TD>
{else}
	<TD>{$item.name}</TD>
{/if}
	<TD><a href='{$LINK_START}module=users&m=departs&action=delete&id={$item.id}'>Удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
{if access('users','change')}
      <DIV style="WIDTH: 1px; HEIGHT: 30px"><IMG height=30 alt="" 
      src="<?=IMAGES_PATH;?>1.gif" width=1></DIV>
      <H3>Добавить отдел:</H3>
<FORM action="?" method=get>
<input type=hidden name=action value=add>
<input type=hidden name=module value=users>
<input type=hidden name=m value=departs>

      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TR><TD class=left>Имя отдела:</TD>
          <TD><input name=name class=text></TD>
          </TR>
          </TBODY></TABLE>
      <HR>

<DIV align=center><INPUT class=button type=submit value="Создать отдел"></DIV>
</FORM>
{/if}
