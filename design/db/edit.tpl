<H2>База данных</H2>
<H3>{$query_table}:</H3>
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
<TR><TD class=left>{$key}:</TD><TD>
{if ($item.show&1)==1}
<input name="row[{$key}]" value="{$item.value}"> 
{/if}
{if ($item.show&2)==2}
<SELECT name="row[{$key}{if $item.show==3}_{/if}]">
{foreach from=$item.variants item=i_item name=inner}
<option value={$i_item.key}{if $item.value==$i_item.key} selected{/if}>{$i_item.show}</option>
{/foreach}
{if $item.show==3}<option value=nouse selected>использовать текстовое поле</option>{/if}
</SELECT>
{/if}</TD></TR>
{/foreach}

</TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
</form>