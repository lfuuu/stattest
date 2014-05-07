{if $dbform_h2}<h2>{$dbform_h2}</h2>{/if}
{if $dbform_h3}<h3>{$dbform_h3}</h3>{/if}
<FORM action="?" method=post id=dbform name=dbform>
{foreach from=$dbform_params item=item key=key}
<input type=hidden name={$key} value='{$item}'>
{/foreach}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>{if isset($smarty.session.trash) && $smarty.session.trash.price_voip}<tr><td><a href='?module=tarifs&action=csv_upload' target='_blank' style='text-decoration:none'>Пакетная заливка</a></td><td>&nbsp;</td></tr>{/if}
{foreach from=$dbform_data item=item key=key name=outer}{if $item.type=='include'}{include file=$item.file}
{elseif $item.type=='no'}
{elseif $item.type=='hidden'}
<input type=hidden name=dbform[{$key}] value='{$item.value}' id={$key}>
{elseif $item.type=='label'}
<input type=hidden name=dbform[{$key}] value='{$item.value}' id={$key}>
<TR id=tr_{$key}><TD class=left width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</TD><TD{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.value}{$item.comment}</TD></TR>

{elseif $item.type=='text' || $item.type=='password'}
<TR id=tr_{$key}>
    <TD class=left width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</TD>
    <TD{if $hl==$key} style="background-color: #EEE0B9"{/if}>
        <input class=text type={$item.type} name=dbform[{$key}] id={$key} {if $key eq "E164"}onchange='optools.voip.check_e164.set_timeout_check(this);'onkeyup='optools.voip.check_e164.set_timeout_check(this);'{/if} value='{$item.value}'{$item.add}>{$item.comment}

{if $key eq "E164"}
	<img src="{$PATH_TO_ROOT}images/icons/disable.gif" id="e164_flag_image" style="visibility:hidden" />
	<span style="visibility:hidden" id="e164_flag_letter">Используется!</span>
	<script type='text/javascript'>optools.voip.check_e164.old_number='{$item.value}';</script>
	<select id='get_free_e164' alt='Получить свободный номер' onchange='optools.voip.check_e164.get_free_e164(this)'>
		<option value='null'>Cвободный номер</option>
		<option value='short'>Короткий номер</option>
        {if $region eq '99'}
            <option value='7499685'>7(499) 685</option>
            <option value='7499213'>7(499) 213</option>
            <option value='7495105'>7(495) 105</option>
            <option value='7495'>7(495)</option>
        {elseif $region eq '97'}
            <option value='7861204'>7(861) 204</option>
        {elseif $region eq '98'}
            <option value='7812'>7(812)</option>
        {elseif $region eq '95'}
            <option value='7343302'>7(343) 302</option>
        {elseif $region eq '96'}
            <option value='7846215'>7(846) 215</option>
        {elseif $region eq '94'}
            <option value='7383312'>7(383) 312</option>
        {elseif $region eq '87'}
            <option value='7863309'>7(863) 309</option>
        {/if}
	</select>
{/if}
{if $key == "actual_from" || $key == "actual_to"}
    <input type=button value="С" title="Сейчас" onclick='var d = new Date(); document.getElementById("{$key}").value="{php} echo date("Y-m-d");{/php}"'>
    <input type=button value="&#8734;" title="Услуга открыта" onclick='document.getElementById("{$key}").value="2029-01-01"' style="">
{/if}
</TD></TR>
{elseif $item.type=='first_text'}
{if $item.value}
<input type=hidden name=dbform[{$key}] value='{$item.value}'>
<TR id=tr_{$key}><TD class=left width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</TD><TD{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.value}</TD>{$item.comment}</TR>
{else}
<TR id=tr_{$key}><TD class=left width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</TD><TD{if $hl==$key} style="background-color: #EEE0B9"{/if}><input class=text type=text name=dbform[{$key}] id={$key} value='{$item.value}'{$item.add}>{$item.comment}</TD></TR>
{/if}
{elseif $item.type=='select'}
<TR id=tr_{$key}><TD class=left width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</TD><TD{if $hl==$key} style="background-color: #EEE0B9"{/if}><select id={$key} name=dbform[{$key}]{$item.add}>
{foreach from=$item.enum item=var name=inner}
<option value='{$var}'{if $item.value==$var} selected{/if}>{$var}</option>
{/foreach}{foreach from=$item.assoc_enum item=var key=vkey name=inner}
<option value='{$vkey}'{if $item.value==$vkey} selected{/if}>{$var}</option>
{/foreach}
</select>{$item.comment}</TD></TR>
{/if}{/foreach}

{foreach from=$dbform_includesForm item=item name=outer}{include file=$item}{/foreach}
</TBODY></TABLE>
{if count($dbform_includesPreL) || count($dbform_includesPreR)}
	<table cellspacing=0 cellpadding=2 border=0 width=100%><TR><TD valign=top>
		{foreach from=$dbform_includesPreL item=item name=outer}{include file=$item}{/foreach}
	</TD><TD valign=top>
		{foreach from=$dbform_includesPreR item=item name=outer}{include file=$item}{/foreach}
	</TD></TR></table>
{/if}
{foreach from=$dbform_includesPre item=item name=outer}{include file=$item}{/foreach}

<DIV align=center><INPUT id=b_submit class=button {if $dbform_table=="usage_voip"} type=button onclick="if(optools.check_submit()) document.getElementById('dbform').submit();"{elseif $dbform_table=="usage_virtpbx"} type=button onclick="if(optools.check_vpbx_submit()) document.getElementById('dbform').submit();"{else} type=submit{/if} value="{if $dbform_btn_new}Добавить{else}Изменить{/if}"></DIV>

{foreach from=$dbform_includesPost item=item name=outer}{include file=$item}{/foreach}
</form>
{foreach from=$dbform_includesPost2 item=item name=outer}{include file=$item}{/foreach}

{if count($dbform_log_usage_history) > 0}
	<br />
	<h3>История изменений услуги</h3>
	{include file='log_usage_history.inc'}
{/if}



