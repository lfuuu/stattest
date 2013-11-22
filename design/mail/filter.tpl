<H2>Письма клиентам. <a href='{$LINK_START}&module=mail&action=view&id={$mail_id}'>Письмо &#8470;{$mail_id}</a></H2>
<H3>Добавление клиентов в очередь на отправку писем</H3>
{if count($mail_clients)}
<TABLE class=price cellSpacing=4 cellPadding=2 border=0 style='width:*' width="*">
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=client>
<input type=hidden name=id value={$mail_id}>
<input type=hidden name=module value=mail>
<TBODY>
<TR>
  <TD class=header vAlign=bottom>Клиент</TD>
  <TD class=header valign=bottom><input type=checkbox id='allconfirm' checked onclick='javascript:check_all()'></td>
  <TD class=header valign=bottom><input type=checkbox id='allconfirm2' checked onclick='javascript:check_all2()'></td>
  <TD>&nbsp;</TD>
  </TR>
{foreach from=$mail_clients item=r name=outer}{if $r.letter_state!="sent"}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD><input type=hidden value='{$r.client}' name='clients[{$smarty.foreach.outer.iteration}]'><a href='{$LINK_START}module=clients&id={$r.client}'>{$r.client}</a></TD>
	<TD><input type=checkbox value=1 name='flag[{$smarty.foreach.outer.iteration}]' id='flag_{$smarty.foreach.outer.iteration}'{if $r.filtered} checked{/if}></TD>
	<TD><input type=checkbox value=1 name='flag2[{$smarty.foreach.outer.iteration}]' id='flag2_{$smarty.foreach.outer.iteration}'{if $r.selected} checked{/if}></TD>
	<TD><input type=hidden value='{$r.email}' name='emails[{$smarty.foreach.outer.iteration}]'>{$r.email}</TD>
</TR>
{/if}{/foreach}
</TBODY></TABLE>
<INPUT id=submit class=button type=submit value="Добавить всех этих клиентов в список на отправку">
</FORM>
<script>
function check_all(){ldelim}
	v=form.allconfirm.checked;
{foreach from=$mail_clients item=r name=outer}{if $r.filtered && $r.letter_state!="sent"}
	form.flag_{$smarty.foreach.outer.iteration}.checked=v;
{/if}{/foreach}
{rdelim}
function check_all2(){ldelim}
	v=form.allconfirm2.checked;
{foreach from=$mail_clients item=r name=outer}{if $r.selected && $r.letter_state!="sent"}
	form.flag2_{$smarty.foreach.outer.iteration}.checked=v;
{/if}{/foreach}
{rdelim}
</script>
{/if}

<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<FORM action="?" method=post id=form2 name=form2>
<input type=hidden name=action value=client>
<input type=hidden name=id value={$mail_id}>
<input type=hidden name=module value=mail>
<input type=hidden name=ack value=1>
<tbody>
<TR><TD>Статус клиента</TD><TD>
<select name='filter[status][0]'><option value='NO'>(не фильтровать по этому полю)</option>{foreach from=$f_status item=r key=k}<option value={$k}>{$r.name}</option>{/foreach}</select>
</td></tr>
<TR><TD>Менеджер</TD><TD>
<select name='filter[manager][0]'><option value='NO'>(не фильтровать по этому полю)</option>{foreach from=$f_manager item=r}<option value='{$r.user}'{if $r.user==$mail_filter.manager.0} selected{/if}>{$r.name} ({$r.user})</option>{/foreach}</select>
</td></tr>
<tr><td>Счета</TD><TD>
<select name='filter[bill][0]'><option value='NO'>(не фильтровать по этому полю)</option>
<option value='1'>любые</option>
<option value='2'>полностью неоплаченные</option>
<option value='3'>оплаченные не полностью</option>
</select>
</option></select>
с <input type=text name='filter[bill][1]' value='{0|mdate:"Y-m-01"}'>
по <input type=text name='filter[bill][2]' value='{0|mdate:"Y-m-31"}'>
</td></tr>
<tr><td>Услуга: 8800</TD><TD>
<select name='filter[s8800][0]'><option value='NO'>(не фильтровать по этому полю)</option>
<option value='with'{if $mail_filter.s8800.0 == 'with'} selected{/if}>с услугой</option>
<option value='without'{if $mail_filter.s8800.0 == 'without'} selected{/if}>без услуги</option>
</select>
</option></select>
</td></tr>
<tr><td colspan=2>
<INPUT id=submit class=button type=submit value="Фильтр">
</td></tr>
</tbody></form></table>
