<H2>База данных</H2>
<H3>{$query_table}:</H3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY><TR>
<TD class=header>&nbsp;</TD>
{foreach from=$query_rows item=item key=k name=outer}
<TD class=header vAlign=bottom><b>{$k}</b></TD>
{/foreach}
</TR>

{foreach from=$query_data item=item name=outer}
<TR>
<TD><a href='{$LINK_START}&module=db&table={$query_table}&action=delete{$item._query}'>x</a> <a href='{$LINK_START}&module=db&table={$query_table}&action=delete&linked={$item._query}'>y</a></TD>
{foreach from=$query_rows item=f key=k name=inner}{if $f.key==1}
<TD><a href='{$LINK_START}&module=db&table={$query_table}&action=edit{$item._query}'>{$item.$k}</a></TD>
{else}{if $f.link_table!=''}
<TD><a href='{$LINK_START}&module=db&table={$f.link_table}&keys[{$f.link_field}]={$item.$k}'>{$item.$k}</a></TD>
{else}
<TD>{$item.$k}</TD>
{/if}{/if}
{/foreach}
</TR>
{/foreach}

</TBODY></TABLE>
<a href='{$LINK_START}&module=db&table={$query_table}&action=edit'>Добавить</a>