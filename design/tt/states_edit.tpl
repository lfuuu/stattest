<H2>Заявки</H2>
<H3>{$query_table}:</H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=sapply>
<input type=hidden name=module value=tt>
<input type=hidden name=dbaction value=apply>
<input type=hidden name=id value={$id}>
<input type=hidden name=table value={$query_table}>
{foreach from=$row item=item key=key name=outer}
<input type=hidden name=old[{$key}] value="{$item.value}">
{/foreach}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR><TD width=50% class=left>id:</TD><td>{$id}</td></tr>
<TR><TD class=left>Название:</TD><td><input type=text value='{$row.name.value}' name=row[name]></td></tr>
<TR><TD class=left>Порядок (используется только при выводе списка):</TD><td><input type=text value='{$row.order.value}' name=row[order]></td></tr>
<TR><TD class=left>Увеличивать время трабла на (в часах; отрицательное число - для уменьшения):</TD><td><input type=text value='{$row.time_delta.value}' name=row[approx]></td></tr>
</TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
</form>