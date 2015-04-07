{if $dbform_h2}<h2>{$dbform_h2}</h2>{/if}
{if $dbform_h3}<h3>{$dbform_h3}</h3>{/if}
{assign var="use_datepicker" value="false"}
{if !isset($hl)}
    {assign var="hl" value=''}
{/if}
<form action="?" method=post id=dbform name=dbform>
    {foreach from=$dbform_params item=item key=key}
        <input type=hidden name={$key} value='{$item}'>
    {/foreach}
    <table class="table table-condensed table-striped">
        {if isset($smarty.session.trash) && $smarty.session.trash.price_voip}
            <tr>
                <td><a href='?module=tarifs&action=csv_upload' target='_blank' style='text-decoration:none'>Пакетная заливка</a></td>
                <td>&nbsp;</td>
            </tr>
        {/if}
        {foreach from=$dbform_data item=item key=key name=outer}
            {if isset($item.visible)}
                {assign var="visible" value=$item.visible}
            {else}
                {assign var="visible" value=1}
            {/if}
            {if $item.type=='include'}
                {include file=$item.file}
            {elseif $item.type=='no'}
            {elseif $item.type=='checkbox'}
                <tr id=tr_{$key} {if !$visible}style="display:none;"{/if}>
                    <td align=right width=40%>{$item.caption}</td>
                    <td>
                        <input type=hidden name=dbform[{$key}] value='0' id="hidden_{$key}">
                        <input type=checkbox name=dbform[{$key}] value='1' id={$key} {if $item.value==1}checked{/if}>
                    </td>
                </tr>
            {elseif $item.type=='hidden'}
                <input type=hidden name=dbform[{$key}] value='{$item.value}' id={$key}>
            {elseif $item.type=='label'}
                <input type=hidden name=dbform[{$key}] value='{$item.value}' id={$key}>
                <tr id=tr_{$key}>
                    <td align=right width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</td>
                    <td{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.value}{$item.comment}</td>
                </tr>
            {elseif $item.type=='text' || $item.type=='password'}
                <tr id=tr_{$key}>
                    <td align=right width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</td>
                    <td{if $hl==$key} style="background-color: #EEE0B9"{/if}>
                        <input class=text type={$item.type} {if $key == "actual_from" || $key == "actual_to"}readonly{/if} name=dbform[{$key}] id={$key} {if $key eq "E164"}onchange='optools.voip.check_e164.set_timeout_check(this);form_usagevoip_hide();' onkeyup='optools.voip.check_e164.set_timeout_check(this);form_usagevoip_hide();'{/if} value='{$item.value}'{$item.add}>{$item.comment}
                        {if $key eq "E164"}
	                        <img src="{$PATH_TO_ROOT}images/icons/disable.gif" id="e164_flag_image" style="visibility:hidden" />
	                        <span style="visibility:hidden" id="e164_flag_letter">Используется!</span>
	                        <script type='text/javascript'>optools.voip.check_e164.old_number='{$item.value}';</script>
                            {if $dbform_data.id.value}
                                <script>$("#E164").attr("readonly", "readonly");</script>
                            {else}
                                <select id='get_free_e164' alt='Получить свободный номер' onchange='optools.voip.check_e164.get_free_e164(this)'>
                                    <option value='0000'>----</option>
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
                                    {elseif $region eq '93'}
                                        <option value='7843207'>7(843) 207</option>
                                    {elseif $region eq '81'}
                                        <option value='36'>36</option>
                                    {elseif $region eq '82'}
                                        <option value='7862'>7(863) 2</option>
                                    {elseif $region eq '83'}
                                        <option value='74212'>7(421) 2</option>
                                    {elseif $region eq '84'}
                                        <option value='7347'>7(347)</option>
                                    {elseif $region eq '85'}
                                        <option value='74832'>7(483) 2</option>
                                    {elseif $region eq '86'}
                                        <option value='7473'>7(473)</option>
                                    {elseif $region eq '88'}
                                        <option value='7831'>7(831)</option>
                                    {elseif $region eq '90'}
                                        <option value='7351'>7(351)</option>
                                    {elseif $region eq '91'}
                                        <option value='78442'>7(844) 2</option>
                                    {elseif $region eq '92'}
                                        <option value='7342'>7(342)</option>
                                    {elseif $region eq '89'}
                                        <option value='7423206'>7(423) 206</option>
                                    {elseif $region eq '87'}
                                        <option value='7863309'>7(863) 309</option>
                                    {/if}


                                </select>
                            {/if}
                        {/if}
                        {if $key == "actual_from" || $key == "actual_to"}
                            <input type=button value="С" title="Сейчас" onclick='var d = new Date(); document.getElementById("{$key}").value="{php} echo date("d-m-Y");{/php}";change_datepicker_option("{$key}");{if $key == "actual_from" && ($dbform_table == "usage_voip"  || $dbform_table == "usage_virtpbx")} optools.voip.check_e164.move_checking();{/if}'>
                            <input type=button value="&#8734;" title="Услуга открыта" onclick='document.getElementById("{$key}").value="01-01-4000";change_datepicker_option("{$key}");{if $key == "actual_from" && ($dbform_table == "usage_voip" || $dbform_table == "usage_virtpbx")} optools.voip.check_e164.move_checking();{/if}' style="">
                            {assign var="use_datepicker" value="true"}
                        {/if}
                    </td>
                </tr>
            {elseif $item.type=='first_text'}
                {if $item.value}
                    <input type=hidden name=dbform[{$key}] value='{$item.value}'>
                    <tr id=tr_{$key}>
                        <td align=right width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</td>
                        <td{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.value} {$item.comment}</td>
                    </tr>
                {else}
                    <tr id=tr_{$key}>
                        <td align=right width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</td>
                        <td{if $hl==$key} style="background-color: #EEE0B9"{/if}><input class=text type=text name=dbform[{$key}] id={$key} value='{$item.value}'{$item.add}>{$item.comment}</td>
                    </tr>
                {/if}
            {elseif $item.type=='select'}
                <tr id=tr_{$key} {if !$visible}style="display:none;"{/if}>
                    <td align=right width=40%{if $hl==$key} style="background-color: #EEE0B9"{/if}>{$item.caption}</td>
                    <td{if $hl==$key} style="background-color: #EEE0B9"{/if}>
                        {if isset($item.with_hidden) && $item.with_hidden}
                            <input type="hidden" name="dbform[{$key}]" value="">
                        {/if}
                        <select id={$key} name=dbform[{$key}]{$item.add} {if !$visible}disabled{/if}>
                            {foreach from=$item.enum item=var name=inner}
                                <option value='{$var}'{if $item.value==$var} selected{/if}>{$var}</option>
                            {/foreach}
                            {foreach from=$item.assoc_enum item=var key=vkey name=inner}
                                <option value='{$vkey}'{if $item.value==$vkey} selected{/if}>{$var}</option>
                            {/foreach}
                        </select>
                        {$item.comment}
                    </td>
                </tr>
            {/if}
        {/foreach}
        
        {if $dbform_table == "usage_voip"}
            <script>
                form_usagevoip_hide();
            </script>
        {/if}
        
        {if $dbform_table == "usage_voip" || $dbform_table == "usage_virtpbx"}
            <script>
                    optools.voip.check_e164.move_checking(1);
            </script>
        {/if}

        {foreach from=$dbform_includesForm item=item name=outer}{include file=$item}{/foreach}
    </table>
    {if count($dbform_includesPreL) || count($dbform_includesPreR)}
	    <table cellspacing=0 cellpadding=2 border=0 width=100%>
            <tr>
                <td valign=top>
		            {foreach from=$dbform_includesPreL item=item name=outer}{include file=$item}{/foreach}
	            </td>
                <td valign=top>
    		        {foreach from=$dbform_includesPreR item=item name=outer}{include file=$item}{/foreach}
    	        </td>
            </tr>
        </table>
    {/if}
    {foreach from=$dbform_includesPre item=item name=outer}{include file=$item}{/foreach}

<DIV align=center>
    <input id=b_submit class=button 
    {if $dbform_table=="usage_voip"}{literal} 
        type=button onclick="
            if(optools.check_submit() && ($('#voip_ats3_add').length ? checkVoipAts3Add() : true)) {
                document.getElementById('dbform').submit();
            }
            "{/literal}
    {elseif $dbform_table=="usage_virtpbx"} 
        type=button onclick="
            if(optools.check_vpbx_submit()) 
                document.getElementById('dbform').submit();"
    {else}
        type=submit
    {/if} value="{if $dbform_btn_new}Добавить{else}Изменить{/if}">
</DIV>

    {foreach from=$dbform_includesPost item=item name=outer}{include file=$item}{/foreach}
</form>

{foreach from=$dbform_includesPost2 item=item name=outer}{include file=$item}{/foreach}

{if isset($dbform_log_usage_history) && count($dbform_log_usage_history) > 0}
	<br />
	<h3>История изменений услуги</h3>
	{include file='log_usage_history.inc'}
{/if}

{if $use_datepicker}
<script>
    optools.DatePickerInit('', 'actual');
    {literal}
    function change_datepicker_option(key)
    {
        if (key == 'actual_to')
        {
            $('#actual_from').datepicker( 'option', 'maxDate', $('#actual_to').val() );
        } else {
            $('#actual_to').datepicker( 'option', 'minDate', $('#actual_from').val() );
        }
        {/literal}
        {if $dbform_table == "usage_voip"}
            change_datepicker_value();
        {elseif $dbform_table == "usage_virtpbx"}
            optools.voip.check_e164.move_checking();
        {/if}
        {literal}
    }
    function change_datepicker_value()
    {
        el = document.getElementById('E164');
        optools.voip.check_e164.set_timeout_check(el);
    }
    {/literal}
</script>
{/if}

