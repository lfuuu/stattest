<H2>Мониторинг</H2>
<H3>Редактирование VIP-клиента:</H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=apply>
<input type=hidden name=module value=monitoring>
<input type=hidden name=dbaction value=apply>
<input type=hidden name=id value={$id}>
<input type=hidden name=table value={$query_table}>
{foreach from=$row item=item key=key name=outer}
<input type=hidden name=old[{$key}] value="{$item.value}">
{/foreach}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
{foreach from=$row item=item key=key name=outer}
{dbmap_element}
{/foreach}

</TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
</form>