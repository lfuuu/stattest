<meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
<H3>PPP-логин:</H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=apply>
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

<TR><TD class=left>Подключение:</TD><TD>
<SELECT name="row[port_id]">
{foreach from=$ports key=key item=item}
<option value="{$item.id}"{if $row.port_id.value==$item.id} selected{/if}>{$item.id} | {$item.address} | {$item.node}</option>
{/foreach}
</SELECT></TD></TR>

</TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
</form>