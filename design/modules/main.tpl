<H2>Модули</H2>
<H3>Список модулей</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="80%" border=0>
<TBODY>
<TR>
  <TD class=header vAlign=bottom width="75%">Модуль</TD>
  <TD class=header vAlign=bottom width="20%">&nbsp;</TD>
  <TD class=header vAlign=bottom width="5%">&nbsp;</TD>
  </TR>
{foreach from=$modules item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD>{$item.module}</TD>
{if ($item.is_installed)}
	<TD><a href='{$LINK_START}module=modules&id={$item.module}&action=uninstall'>Деинсталлировать</a></TD>
{else}
	<TD><a href='{$LINK_START}module=modules&id={$item.module}&action=install'>Инсталлировать</a></TD>
{/if}
	<TD><a href='{$LINK_START}module=modules&id={$item.module}&action=up'>&#9650;</a> <a href='{$LINK_START}module=modules&id={$item.module}&action=down'>&#9660;</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
