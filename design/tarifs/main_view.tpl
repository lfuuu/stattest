<H2>Тарифы</H2>
<H3>{$tarifs_title}:</H3>
Показывать: <a href='{$LINK_START}&module=tarifs&m={$m}&filter=public'>общие</a> / <a href='{$LINK_START}&module=tarifs&m={$m}&filter=special'>специальные</a>  / <a href='{$LINK_START}&module=tarifs&m={$m}&filter=archive'>архивные</a><br>
{if access('tarifs','edit')}
<a href='{$LINK_START}&module=tarifs&m={$m}&action=add'>Добавить</a>
{/if}<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY><TR>
{foreach from=$tarifs_fields item=item name=outer}
<TD class=header vAlign=bottom><b>{if isset($tarifs_translate[$item])}{$tarifs_translate[$item]}{else}{$item}{/if}</b></TD>
{/foreach}
</TR>

{foreach from=$tarifs_data item=item name=outer}
<TR>
{foreach from=$tarifs_fields item=f name=inner}
{if in_array($f,$tarifs_keys)}
<TD><a href='{$LINK_START}&module=tarifs&m={$m}&action=edit&id={$item.id}'>{$item.$f}</a></TD>
{else}
<TD>{$item.$f}</TD>
{/if}
{/foreach}
</TR>
{/foreach}

</TBODY></TABLE>
