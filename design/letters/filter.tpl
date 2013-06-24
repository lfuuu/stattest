<H2>Рассылки</H2>
<H3>Добавление клиентов</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0 style='width:*' width="*">
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=assign>
<input type=hidden name=letter value={$letters_letter}>
<input type=hidden name=module value=letters>
<TBODY>
<TR>
  <TD class=header vAlign=bottom>Клиент</TD>
  <TD class=header valign=bottom><input type=checkbox id='allconfirm' onclick='javascript:check_all()'></td>
  <TD>&nbsp;</TD>
  </TR>
{foreach from=$letters_clients item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD><input type=hidden value='{$item.client}' name='clients[{$smarty.foreach.outer.iteration}]'><a href='{$LINK_START}module=clients&id={$item.client}'>{$item.client}</a></TD>
	<TD><input type=checkbox value=1 name='flag[{$smarty.foreach.outer.iteration}]' id='flag_{$smarty.foreach.outer.iteration}'></TD>
	<TD><input type=hidden value='{$item.email}' name='emails[{$smarty.foreach.outer.iteration}]'>{$item.email}</TD>
</TR>
{/foreach}
</TBODY></TABLE>
<INPUT id=submit class=button type=submit value="Подтвердить">
</FORM>
<script>
function check_all(){ldelim}
	v=form.allconfirm.checked;
{foreach from=$letters_clients item=item name=outer}
	form.flag_{$smarty.foreach.outer.iteration}.checked=v;
{/foreach}
{rdelim}
</script>
