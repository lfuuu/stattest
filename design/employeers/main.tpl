<H2>Сотрудники</H2>
{if isset($emp_user)}
<h3>{$emp_user.name}</h3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width=80% border=0>
<TBODY>
	<TR><TD class=left>Логин:</TD><TD>
	{$emp_user.user}
	</TD></TR>
	<TR><TD class=left>Группа:</TD><TD>
	{$emp_user.usergroup}
	</TD></TR>
	<TR><TD class=left>Полное имя:</TD><TD>
	{$emp_user.name}
	</TD></TR>
	<TR><TD class=left>Перенаправление траблов:</TD><TD>
	{$emp_user.trouble_redirect}
	</TD></TR>
	<TR><TD class=left>e-mail:</TD><TD>
	{$emp_user.email}
	</TD></TR>
	<TR><TD class=left>ICQ:</TD><TD>
	{$emp_user.icq}
	</TD></TR>

	<TR><TD colspan=2 align=center><img src='images/users/{$emp_user.id}.{$emp_user.photo}'>
	</TD></TR>
</TABLE>
{/if}

<FORM action="?" method=get id=form name=form>
<input type=hidden name=module value=employeers>
<select name=group>
{foreach from=$emp_groups item=item name=outer}
<option value="{$item.usergroup}"{if isset($emp_group) && ($emp_group.usergroup==$item.usergroup)} selected{/if}>{$item.comment} - {$item.usergroup}</option>
{/foreach}
</select>
<input type=submit value='Посмотреть' class=button>
</form>

{if isset($emp_group)}
<h3>{$emp_group.usergroup}</h3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR><TD class=header vAlign=bottom>Login<br>Полное имя</TD>
<TD class=header vAlign=bottom>Фото?</TD></TR>

{foreach from=$emp_users item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
<TD valign=center><a href='{$LINK_START}module=employeers&user={$item.user}'>{$item.user}</a><br>{$item.name}</TD>
<TD>{if $item.photo}<img src='images/users/{$item.id}.{$item.photo}'>{else}&nbsp;{/if}</TD></TR>
{/foreach}
</TBODY></TABLE>
{/if}