<H2>Тарифы</H2>
<H3>{$tarifs_title}:</H3>
{if access('tarifs','edit')}
<FORM action="?" method=post id=form name=form>
<input type=hidden name=module value=tarifs>
<input type=hidden name=m value={$m}>
<input type=hidden name=action value=apply>
<input type=hidden name=id value='{$tarifs_data.id}'>
{/if}

<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
{foreach from=$tarifs_fields item=item name=outer}
<TR><TD class=left width=40%>{if isset($tarifs_translate[$item])}{$tarifs_translate[$item]}{else}{$item}{/if}</TD>
<TD><input style='width:100%' name={$item} value='{$tarifs_data.$item}'></TD></TR>
{/foreach}

{include file=$tarifs_inc}

</TBODY></TABLE>
{if access('tarifs','edit')}
<DIV align=center><input id=submit class=button type=submit value="Изменить"></DIV></FORM>
<a href='{$LINK_START}module=tarifs&m={$m}&id={$tarifs_data.id}&action=delete'>Удалить</a>
{/if}