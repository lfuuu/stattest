<H2>Отправка счетов</H2>
<H3>Подтверждение</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0 style='width:*' width="*">
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=confirm>
<input type=hidden name=module value=send>
<TBODY>
<TR>
  <TD class=header vAlign=bottom>Клиент</TD>
  <TD class=header vAlign=bottom>Менеджер</TD>
  <TD class=header vAlign=bottom>Счёт</TD>
  <TD class=header vAlign=bottom>Дата счёта</TD>
  <TD class=header valign=bottom>e-mail</td>
  <TD class=header valign=bottom><input type=checkbox checked id='allconfirm' onclick='javascript:check_all()'></td>
  </TR>
{foreach from=$send_confirms item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD><input type=hidden value='{$item.client}' name='bill_client[{$smarty.foreach.outer.iteration}]'><a href='/client/view?id={$item.clientid}'>{$item.client}</a></TD>
	<TD>{$item.manager}</TD>
	<TD><input type=hidden value='{$item.bill_no}' name='bill_no[{$smarty.foreach.outer.iteration}]'><a href='modules/accounts/view.php?bill_no={$item.bill_no}&client={$item.client}'>{$item.bill_no}</a></TD>
	<TD>{$item.bill_date}</TD>
	<TD><input type=hidden value='{$item.email}' name='bill_email[{$smarty.foreach.outer.iteration}]'>{$item.email}</TD>
	<TD><input type=checkbox {if $item.email!="" && $item.fday}checked {/if}value=1 name='bill_confirmed[{$smarty.foreach.outer.iteration}]' id='bill_confirmed_{$smarty.foreach.outer.iteration}'> <a href='{$LINK_START}module=clients&id={$item.client}&action=edit_pop&hl=email' target=_blank>редактировать</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
<INPUT id=submit class=button type=submit value="Подтвердить">
</FORM>
<script>
function check_all(){ldelim}
	v=form.allconfirm.checked;
{foreach from=$send_confirms item=item name=outer}
	form.bill_confirmed_{$smarty.foreach.outer.iteration}.checked=v;
{/foreach}
{rdelim}
</script>
