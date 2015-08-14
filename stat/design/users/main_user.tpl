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
		<TR><TD class=left>Язык:</TD><TD>
		<select name="language"><option value="ru-RU" {if $user.language == 'ru-RU'}selected{/if}>Русский</option><option value="hu-HU" {if $user.language == 'hu-HU'}selected{/if}>Magyar</option></select>
		</TD></TR>
		<TR><TD class=left>Внутренний номер (логин в comcenter):</TD><TD>
		<input name=phone_work class=text value='{$user.phone_work}'>
		</TD></TR>
		<TR><TD class=left>Телефон мобильный:</TD><TD>
		<input name=phone_mobile class=text value='{$user.phone_mobile}'>
		</TD></TR>
		<TR><TD class=left>ICQ:</TD><TD>
		<input name=icq class=text value='{$user.icq}' autocomplete="off">
		</TD></TR>

		<TR><TD class=left>Фотография:</TD><TD>
		<input style='width:60%' name=photo class=text type=file value='' onchange='javscript:photo_change.checked=true;'><input id=file_change value=1 class=text type=checkbox name=photo_change>
		</TD></TR>
		
		<TR><TD class=left>Пароль: (если оставить пустым - не изменится)</TD><TD>
		<input name=pass1 class=text type=password value='' autocomplete="off">
		</TD></TR>
		<TR><TD class=left>Пароль ещё раз:</TD><TD>
		<input name=pass2 class=text type=password value='' autocomplete="off">
		</TD></TR>  

		<TR><TD class=left>Пользователь активен:</TD><TD>
		<input type=checkbox value="yes" name=enabled{if $user.enabled=='yes'} checked{/if}>
		</TD></TR>  

		<TR><TD class=left>Привязка к курьеру:</TD><TD>
		<select name='courier_id'>
			<option value="0">Нет</option>
            {foreach from=$couriers item='cs' key='depart'}
				<optgroup label="{$depart}">
				{foreach from=$cs item='name' key='id'}<option value='{$id}'{if $user.courier_id == $id} selected{/if}>{$name}</option>{/foreach}
				</optgroup>
            {/foreach}
		</select>
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

{if access('users','grant')}
<H2>Права доступа</H2>
      <TABLE cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
{counter start=0 assign=CNT}
{foreach from=$rights item=supitem key=supkey name=outer}
	<TR>
		<TD colspan=2>
			<span style="font-weight: bold; font-size: 17px;">{$supkey}</span>
		</TD>
	</TR>
	{foreach from=$supitem item=item key=right name=fe}
		<TR>
			<TD vAlign=top>
				<span style="font-weight: bold; font-size: 14px; padding-left: 15px;">{$item.comment} ({$right})</span>
			</TD>
			<TD vAlign=top>
				<div style="margin-bottom: 10px;">
				<input name=rights_radio[{$right}] id="{$right}_0" type=radio onchange="javascript:set_disable('{$right}')" value=0 {if !isset($rights_user.$right)}checked{/if}>
				<label for='{$right}_0' style="font-size: 9px;vertical-align: top; line-height: 18px;">Стандартный</label> 
				<input name=rights_radio[{$right}] id="{$right}_1" type=radio onchange="javascript:set_enable('{$right}')" value=1 {if isset($rights_user.$right)}checked{/if}>
				<label for='{$right}_1' style="font-size: 9px;vertical-align: top; line-height: 18px;">Особый</label>
				</div>
				{assign var="applied_rights" value=","|explode:$rights_real.$right}
				{foreach from=$item.values item=item2 key=key2 name=inner}
					<div>
						<input onchange="set_real_value(this, '{$right}', '{$item2}');" {if !isset($rights_user.$right)}disabled{/if} class="checkbox_{$right}" type="checkbox" id="{$right}_{$item2}"{if $item2|in_array:$applied_rights} checked{/if} value="{$item2}" name="rights[{$right}][]" >
						<label class="label_{$right}" for="{$right}_{$item2}" {if !isset($rights_user.$right)}style="color: #CCCCCC;"{/if}>{$item.values_desc[$key2]} (<b>{$item2}</b>)</label>
					</div>
				{/foreach}
			</TD>
		</TR>
		<tr>
			<td colspan=2><hr style="background-color: #CCCCCC;color: #CCCCCC;"></td>
		</tr>
	{counter}
	{/foreach}
{/foreach}
		</TBODY></TABLE>
	<HR>

<script language=javascript>
var rights_real = [];
var rights_group = [];
{foreach from=$rights item=supitem}
	{foreach from=$supitem item=item key=k}
		rights_group['{$k}'] = [];
		rights_real['{$k}'] = [];
	{/foreach}
{/foreach}
{foreach from=$rights_group item="i" key="k"}
	{assign var="applied_rights" value=","|explode:$i}
	rights_group['{$k}'] = [];
	{foreach from="$applied_rights" item="v"}
		rights_group['{$k}']['{$v}'] = 1; 
	{/foreach}
{/foreach}
{foreach from=$rights_real item="i" key="k"}
	{assign var="applied_rights" value=","|explode:$i}
	rights_real['{$k}'] = [];
	{foreach from="$applied_rights" item="v"}
		rights_real['{$k}']['{$v}'] = 1; 
	{/foreach}
{/foreach}
{literal}
function set_disable(r) {
	$('.checkbox_'+r).attr('disabled', true);
	$('.checkbox_'+r).attr('checked', false);
	$('.label_'+r).css('color', '#CCCCCC');
	for(right in rights_group[r])
	{
		$('#' + r + '_' + right).attr('checked', true);
	}
	return 1;
}
function set_enable(r) {
	$('.checkbox_'+r).attr('disabled', false);
	$('.checkbox_'+r).attr('checked', false);
	$('.label_'+r).css('color', '');
	for(right in rights_real[r])
	{
		if (rights_real[r][right] == 1)
		{
			$('#' + r + '_' + right).attr('checked', true);
		}
	}
	return 1;
}
function set_real_value(elm, r, v)
{
	rights_real[r][v] = elm.checked;
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
