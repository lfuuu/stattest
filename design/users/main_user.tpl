<H2>Операторы</H2>
<H3>Изменение оператора {$user.user}:</H3>
<FORM action="?" method=post id=form name=form enctype="multipart/form-data">
<input type=hidden name=action value=edit>
<input type=hidden name=module value=users>
<input type=hidden name=m value=user>
<input type=hidden name=id value='{$user.user}'>

{if $user.photo}
<table cellspacing=0 cellpadding=0 width=100% border=0>
<tr><td><img src='images/users/{$user.id}.{$user.photo}'></td><td>
{/if}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
	<TBODY>
{if access('users','change')}
		<TR><TD class=left>Логин:</TD><TD>
		<input name=newuser class=text value='{$user.user}'>
		</TD></TR>
		<TR><TD class=left>Группа:</TD><TD>
		<SELECT name=usergroup>{foreach from=$groups item=item}<option value='{$item.usergroup}'{$item.selected}>{$item.usergroup} - {$item.comment}</option>{/foreach}</select>
		</TD></TR>
		<TR><TD class=left>Отдел:</TD><TD>
		<SELECT name=depart_id>{foreach from=$departs item=item}<option value='{$item.id}'{if $item.id==$user.depart_id} selected{/if}>{$item.name}</option>{/foreach}</select>
		</TD></TR>
		<TR><TD class=left>Полное имя:</TD><TD>
		<input name=name class=text value='{$user.name}'>
		</TD></TR>
		<TR><TD class=left>Перенаправление траблов:</TD><TD>
		<SELECT name=trouble_redirect><option value=''{if !$user.trouble_redirect} selected{/if}></option>{foreach from=$users item=item}<option value='{$item.user}'{if $user.trouble_redirect==$item.user} selected{/if}>{$item.user} - {$item.name}</option>{/foreach}</select>
		</TD></TR>
		<TR><TD class=left>e-mail:</TD><TD>
		<input name=email class=text value='{$user.email}'> (<a href='mailto:{$user.email}'>написать</a>)
		</TD></TR>
		<TR><TD class=left>Внутренний номер (логин в comcenter):</TD><TD>
		<input name=phone_work class=text value='{$user.phone_work}'>
		</TD></TR>
		<TR><TD class=left>Телефон мобильный:</TD><TD>
		<input name=phone_mobile class=text value='{$user.phone_mobile}'>
		</TD></TR>
		<TR><TD class=left>ICQ:</TD><TD>
		<input name=icq class=text value='{$user.icq}'>
		</TD></TR>

		<TR><TD class=left>Фотография:</TD><TD>
		<input style='width:60%' name=photo class=text type=file value='' onchange='javscript:photo_change.checked=true;'><input id=file_change value=1 class=text type=checkbox name=photo_change>
		</TD></TR>
		
		<TR><TD class=left>Пароль: (если оставить пустым - не изменится)</TD><TD>
		<input name=pass1 class=text type=password value=''>
		</TD></TR>
		<TR><TD class=left>Пароль ещё раз:</TD><TD>
		<input name=pass2 class=text type=password value=''>
		</TD></TR>  

		<TR><TD class=left>Пользователь активен:</TD><TD>
		<input type=checkbox value="yes" name=enabled{if $user.enabled=='yes'} checked{/if}>
		</TD></TR>  
{else}
		<TR><TD class=left>Логин:</TD><TD>
			{$user.user}
		</TD></TR>
		<TR><TD class=left>Группа:</TD><TD>
			{$user.usergroup} - {$rights.$group.comment}
		</TD></TR>
		<TR><TD class=left>Полное имя:</TD><TD>
			{$user.name}
		</TD></TR>
		<TR><TD class=left>Перенаправление траблов:</TD><TD>
			{$user.trouble_redirect}
		</TD></TR>
		<TR><TD class=left>e-mail:</TD><TD>
			{$user.email} (<a href="mailto: {$user.email}">написать</a>)
		</TD></TR>
		<TR><TD class=left>ICQ:</TD><TD>
			{$user.icq}
		</TD></TR>
{/if}
		</TBODY></TABLE>
{if $user.photo}
	</TD></TR></table>
{/if}

<hr />
<H3>Фирмы</H3>
<table cellSpacing=4 cellPadding=2 border=0>
	<thead>
		<tr><th>Фирма</th><th><input id="check_all" type="checkbox" value="1" /> Доступ</th></tr>
	</thead>
	<tbody>
		{foreach from=$firms item='firma' key='key'}
			<tr><td>{$firma}</td><td><input class="check_firm" type="checkbox" name="user2firm[{$key}]" value="1" {if $user2firm.$key == 1}checked{/if} /></td></tr>
		{/foreach}
	</tbody>
</table>
<hr />

{if access('users','grant')}
<H3>Права доступа</H3>
      <TABLE cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
{counter start=0 assign=CNT}
{foreach from=$rights item=supitem key=supkey name=outer}
		<TR><TD colspan=2><h3>{$supkey}</h3></TD></TR>
{foreach from=$supitem item=item key=right name=fe}
		<TR><TD>{$item.comment} (<b>{$right}</b>)<br>{foreach from=$item.values item=item2 key=key2 name=inner}{if $key2>0}; {/if}<b>{$item2}</b> - {$item.values_desc[$key2]}{/foreach}</TD><TD>
{if !isset($rights_user.$right)}
		<input name=rights_radio[{$right}] id=r0_{$CNT} type=radio onchange="javascript:set_disable({$CNT},'{$rights_group.$right}')" value=0 checked><label for='r0_{$CNT}'>Стандартный</label> <input name=rights_radio[{$right}] id=r1_{$CNT} type=radio onchange="javascript:set_enable({$CNT})" value=1><label for='r1_{$CNT}'>Особый</label>
		<input name=rights[{$right}] id=rights_{$CNT} class=text value='{$rights_real.$right}' disabled>
{else}
		<input name=rights_radio[{$right}] id=r0_{$CNT} type=radio onchange="javascript:set_disable({$CNT},'{$rights_group.$right}')" value=0><label for='r0_{$CNT}'>Стандартный</label> <input name=rights_radio[{$right}] id=r1_{$CNT} type=radio onchange="javascript:set_enable({$CNT})" value=1 checked><label for='r1_{$CNT}'>Особый</label>
		<input name=rights[{$right}] id=rights_{$CNT} class=text value='{$rights_real.$right}'>
{/if}
		</TD></TR>
{counter}
{/foreach}
{/foreach}
		</TBODY></TABLE>
	<HR>
{literal}
<script language=javascript>
function set_disable(p,k) {
	v='rights_'+p;
	document.all[v].disabled = 1;
	document.all[v].value = k;
	return 1;
}
function set_enable(p) {
	v='rights_'+p;
	document.getElementById(v).disabled = 0;
	return 1;
}
$(function(){
	$("#check_all").change(function()
	{
		if ($(this).is(':checked')) $('.check_firm').prop('checked', true);
		else $('.check_firm').prop('checked', false);
	});
});
</script>
{/literal}
{/if}

{if access('users','change')}
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
{/if}
</FORM>
