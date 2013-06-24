<H2>Аппаратура</H2>
<H3>{$query_table}:</H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=r_edit>
<input type=hidden name=module value=routers>
<input type=hidden name=dbaction value=apply>
<input type=hidden name=router value={$router}>
<input type=hidden name=table value={$query_table}>
{foreach from=$row item=item key=key name=outer}
<input type=hidden name=old[{$key}] value="{$item.value}">
{/foreach}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR><TD class=left>Роутер:</TD><TD>{$router}</td></Tr>
{foreach from=$row item=item key=key name=outer}
{dbmap_element}
{/foreach}

</TBODY></TABLE>
{if access('routers_routers','edit')}<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>{/if}
</form>