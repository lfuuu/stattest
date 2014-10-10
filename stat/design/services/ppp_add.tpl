<H2>Услуги</H2>
<H3>PPP-логины</H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=module value=services>
<input type=hidden name=dbaction value=apply>
<input type=hidden name=action value=ppp_apply>
<input type=hidden name=table value={$query_table}>
{foreach from=$row item=item key=key name=outer}
<input type=hidden name=old[{$key}] value="{$item.value}">
{/foreach}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR><TD class=left>Клиент:</TD><TD>{$fixclient}</TD></TR>
{foreach from=$row key=key item=item name=outer}
{dbmap_element}
{/foreach}

<TR><TD class=left>Подключение:</TD><TD>
<SELECT name="row[port_id]">
{foreach from=$ports key=key item=item}
<option value="{$item.id}"{if $row.port_id.value==$item.id} selected{/if}>{$item.id} | {$item.address} | {$item.node}</option>
{/foreach}
</SELECT></TD></TR>
</TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Добавить"></DIV>
</form>