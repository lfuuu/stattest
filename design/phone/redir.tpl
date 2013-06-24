<H2>Виртуальная АТС</H2>
<H3>Переадресация</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR><td class=header>Откуда</td><td class=header>Куда</td><td class=header>Когда</td><TD>&nbsp;</TD></TR>
{foreach from=$voips item=voip name=outer2}
{foreach from=$redirs item=item name=outer}{if $item.voip_id==$voip.id}
<FORM action="?" id=form{$smarty.foreach.outer.iteration} name=form{$smarty.foreach.outer.iteration} method=get><input type=hidden name=module value=phone>
<input type=hidden name=action value=redir_save>
<input type=hidden name=id value={$item.id}>
<TR class="{cycle values='even,odd'}">
	<TD><input class=text type=text value='{$item.E164}' name='voip' disabled></TD>
	<TD><input class=text type=text value='{$item.number}' name='number'></TD>
	<TD><SELECT name=condition_id class=text>
{foreach from=$item.conditions item=cond name=inner}
		<option value={$cond.id}{if $cond.id==$item.condition_id} selected{/if}>{$cond.title}</option>
{/foreach}
  	</SELECT></TD>
	<TD>
		<a href='#' onclick='form{$smarty.foreach.outer.iteration}.submit(); return false;'>изменить</a> 
		<a href='{$LINK_START}module=phone&action=redir_del&id={$item.id}'>удалить</a>
	</TD>
</TR></FORM>
{/if}{/foreach}
{/foreach}

<TR><TD colspan=4><H3>Добавить</H3></TD></TR>

<FORM action="?" id=form_new name=form_new method=get><input type=hidden name=module value=phone>
<input type=hidden name=action value=redir_save>
<TR class="{cycle values='even,odd'}">
	<TD><SELECT name=voip_id class=text>
{foreach from=$voips item=item name=inner}
		<option value={$item.id}>{$item.E164}</option>
{/foreach}
  	</SELECT></TD>
	<TD><input class=text type=text value='{$item.number}' name='number'></TD>
	<TD><SELECT name=condition_id class=text>
{foreach from=$conditions item=cond name=inner}
		<option value={$cond.id}>{$cond.title}</option>
{/foreach}
  	</SELECT></TD>
	<TD>
		<input type=submit class=button value='Добавить' style='width:100%'>
	</TD>
</TR></FORM>


</TBODY></TABLE>
