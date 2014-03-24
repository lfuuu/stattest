{if isset($constructor.check_items)}
<script>
function _check(){literal}{{/literal}
{foreach from=$constructor.check_items item=item}
    var o_{$item} = document.getElement{if $map[$item].type == "radio" || $map[$item].type == "checkbox"}sByName{else}ById{/if}('{$item}{if $map[$item].type == "checkbox"}[]{/if}');
    var v_{$item} = {if $map[$item].type == "select"}
         getSelectValue(o_{$item});
    {elseif ($map[$item].type == "text" || $map[$item].type == "info") }
        o_{$item}.value;
    {elseif $map[$item].type == "radio"}
        getRadiosValue(o_{$item});
    {elseif $map[$item].type == "checkbox"}
        getCheckboxValue(o_{$item});
    {elseif $item == "id"}
        '{$data.id}';
    {/if}

{/foreach}
{foreach from=$map key=name item=item}
    {if isset($item.condition_js)}
        toView("tr_{$name}", {$item.condition_js});
    {/if}
{/foreach}
{literal}}
function getSelectValue(obj) { return obj.options[obj.selectedIndex].value; }
function getRadiosValue(obj) {
    for(var i = 0 ; i < obj.length ; i++) {
        if (obj[i].checked) {
            return obj[i].value;
        }
    }
    return false;
}
function getCheckboxValue(obj)
{
    if(obj[0].checked)
        return obj[0].value;

    return "";
}
function show(elem) { getE(elem).style.display=''; }
function hidd(elem) { getE(elem).style.display='none'; }
function toView(elem, bIsView) {if (bIsView) {show(elem);}else{hidd(elem);}}
function getE(name) { return document.getElementById(name); }

function genPass()
{
    getE('password').value= getPass(15);
}

function getPass(len)
{
    var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var s = "";
    for(i = 0; i < len ; i++)
    {
        s += chars[Math.floor(Math.random()*(chars.length))];
    }
    return s;
}

</script>
<style>
.changed{
       background-color: #aea;
}
</style>


{/literal}
{/if}


{foreach from=$map key=name item=item}
{if isset($item.type) && $item.type != "section"}
    {if $item.type != "hidden" && $item.type != "break"}
    	<TR id="tr_{$name}"{if isset($item.changed) && $item.changed} class="changed"{/if} {if isset($item.condition) && !calcCondition($item.condition, $data)} style="display: none;"{/if}><TD class="td_r">{$item.title}{if isset($item.hint)}<div class="hint">{$item.hint}</div>{/if}:</TD><TD>
    {/if}
	    {if $item.type == "info"}
	        <INPUT type="text" name="{$name}" value="{$data[$name]}" id="{$name}" {*if isset($item.id)} id="{$item.id}"{/if*} readonly>
            <b></b>
	    {elseif $item.type == "password"}
            <INPUT type="text" style="width:150px;" id="{$name}" name="{$name}" value="{$data[$name]}"{if isset($item.check_change)} onChange="_check()"{/if}>
            <input type="button" style="width:80px; font: normal 6pt sans-serif; height:25px;" value="Сгенерировать" onclick="genPass();">
	    {elseif $item.type == "password_ats"}
            {include file="ats/FormConctructor_password_ats.htm"}
	    {elseif $item.type == "password_ats2"}
            {include file="ats2/FormConctructor_password_ats.htm"}
	    {elseif $item.type == "text"}
            <INPUT type="text" name="{$name}" value="{$data[$name]}" {if isset($item.id)} id="{$item.id}"{/if} {if isset($item.check_change)} onChange="_check()"{/if} alt="{$item.mask}">
	        
	    {elseif $item.type == "hidden"}
	        <INPUT type="hidden"  name="{$name}" value="{$data[$name]}"{if isset($item.check_change)} onChange="_check()"{/if}>
	    {elseif $item.type == "select"}
	        <select name="{$name}" id="{$name}"{if isset($item.check_change)} onChange="_check()"{/if}>
	            {html_options options=$constructor.list[$name] selected=$constructor.selected[$name]}
	        </select>
	    {elseif $item.type == "radio"}
	        {foreach from=$constructor.list[$name] key=val item=label}
	        <input id="{$name}_{$val}" type=radio name={$name} value={$val}{if $val==$constructor.selected[$name]} checked{/if}{if isset($item.check_change) || isset($constructor.onchange[$name])} onChange="{if isset($item.check_change)}_check();{/if}{if isset($constructor.onchange[$name])}{$constructor.onchange[$name]};{/if}"{/if}><label for="{$name}_{$val}" style="cursor: pointer;">{$label}</label>
	        {/foreach}
	    {elseif $item.type == "break"}
            </table></td><td width=50% valign=top style="border-left: 3px solid #ccc;"><table><tr><td>
	    {elseif $item.type == "checkbox"}
	        {foreach from=$constructor.list[$name] key=val item=label}
                <label><input type="checkbox" value="{$val}" name="{$name}[]"{if in_array($val, $constructor.selected[$name])} checked{/if}{if isset($item.check_change)} onChange="_check()"{/if}>{$label}</label><br>
            {/foreach}
        {elseif $item.type == "number_lines_select"}
        <script>
        var aNL_{$name} = [
            {foreach from=$constructor.list[$name] item=nl key=nk}
                {literal}{{/literal}id:{$nk}, number:{$nl.number}, call_count:{$nl.call_count} {literal}},{/literal}
            {/foreach}
            ];

{literal}
function _nl_check(obj,name)
{
    getE("number_lines_lines_"+name).value = aNL_{/literal}{$name}{literal}[obj.selectedIndex].call_count;
    var o = document.getElementsByName("lines");
    if(o)
        o[0].value=aNL_{/literal}{$name}{literal}[obj.selectedIndex].call_count;

        document.getElementsByName("line_mask")[0].value = getE("number_lines_"+name).options[getE("number_lines_"+name).selectedIndex].text;
}
{/literal}
        </script>
        <select name="number_lines_{$name}" id="number_lines_{$name}" onChange="_nl_check(this, '{$name}')" style="width: 120px;">
            {foreach from=$constructor.list[$name] item=nl key=nk}
                <option value="{$nk}"{if $constructor.selected[$name] == $nk} selected{/if}>{$nl.number}</option>
            {/foreach}
        </select>
        <input type=text name="number_lines_lines_{$name}" id="number_lines_lines_{$name}" value="{$constructor.list[$name][0].lines}" style="width: 25px;" readonly>
{assign var="number_lines_exec" value=`$name`}

	    {elseif $item.type == "sort_list"}
    
<INPUT type="hidden" id="send_members_{$name}" name="send_members_{$name}" value="">
<script language="JavaScript" src="{$PATH_TO_ROOT}js/manager.js"></script>
	<table border=0><tr><td valign=top>
	<i>Включенные:</i><br>
	<select id=members_{$name} size="{$constructor.list[$name].count}" style="width:120px;border: 1px solid gray;">
{html_options options=$constructor.list[$name].used}
</select>
</td><td valign=top>

<table>
<tr><td><input type=button value="Вверх" onClick="upMember('{$name}')" style="width:80px;border: 1px solid gray;"></td></tr>
<tr><td><input type=button value="Вниз" onClick="downMember('{$name}')" style="width:80px;border: 1px solid gray;"></td></tr>
<tr><td><input type=button value="Добавить" onClick="inMember('{$name}')" style="width:80px;border: 1px solid gray;"></td></tr>
<tr><td><input type=button value="Убрать" onClick="outMember('{$name}')" style="width:80px;border: 1px solid gray;"></td></tr>
</table>

</td><td valign=top>
<i>Возможные:</i><br>
<select id="no_members_{$name}" size="{$constructor.list[$name].count}" style="width:120px;border: 1px solid gray;">
{html_options options=$constructor.list[$name].noused}
</select>
</td></tr></tr></table>

    {elseif $item.type == "textarea"}
        <textarea name="{$name}" style="height: 70px; width: 200px;">{$data[$name]}</textarea>
    {elseif $item.type == "permit_net"}
    <script src="js/permit_net.js"></script>
    {if $name == "numbers_mt"}<script>var is_{$name}_number = true;</script>{/if}
    <input type=hidden id="permit_net_true" name="permit_net_true" value="true">
    <input type=hidden id="permit_net_save_{$name}" name="permit_net_save_{$name}" value="">
        <select name="{$name}" size=6 id="permit_net_{$name}" style="height: 70px; width: 200px;">{html_options options=$data[$name]}</select><input type=button value="Удалить" onclick="doDellIpNet('{$name}');"><br>
        <input type="text" id="permit_add_addr_{$name}" style="width: 120px;"> / <input type="text" id="permit_add_net_{$name}" style="width: 30px;" value="32"> <input type="button" value="Добавить" onclick="doAddIpNet('{$name}')">
    {elseif $item.type == "multitrunk_numbers"}
    <script src="js/multitrunk.js"></script>
    <input type=hidden id="mtn_save" name="mtn_save" value="">
    <table>
    <tr>
    <td>
        <select name="{$name}" size=6 id="mtn" style="height: 70px; width: 200px;" ondblClick="doEditMTN();">{html_options options=$constructor.list[$name].used}</select>
        </td>
        <td>
        <div id="mtn_keys">
        <input type=button value="Редактировать" onclick="doEditMTN();"><br><br>
        <input type=button value="Удалить" onclick="doDellMTN();">
        </div>
        </td>
        </tr>
        </table>
        <div id="mtn_add"{if !$constructor.list[$name].noused} style="display: none;"{/if}>
        <select id="mtn_numbers" style="width: 120px;">{html_options options=$constructor.list[$name].noused}</select>
        <select id="mtn_direction">{html_options options=$direction}</select>
        <input type="button" value="Добавить" onclick="doAddMTN();">
        </div>
        <div id="mtn_edit" style="display: none";>
        <input type=text id="mtn_edit_number" style="width: 120px;" readonly>
        <select id="mtn_edit_direction">{html_options options=$direction}</select>
        <input type="button" value="Изменить" onclick="doApplyMTN();">
        <input type="button" value="Отмена" onclick="doCancelMTN();">
        </div>
    {elseif $item.type == "is_pool"}
            <table>
            <tr>
            <td>
	            <input type="checkbox" id="is_pool" value="yes" name="{$name}[]" onclick="_is_pool_check(this)"{if $data[$name]=="yes"} checked{/if}>
                </td>
                <td>
                <div id="is_pool_div" style="display: none;"><input type=text readonly id='is_pool_pool' style="width: 35px;"> - одновременных звонков</div>
                </td>
                </tr>
                </table>
                <script>

                var countInPool = parseInt("0{$count_in_pool}", 10);
                {literal}
                function _is_pool_check()
{
    var o = getE("is_pool");
    if(o.checked)
    {
        getE("is_pool_pool").value=countInPool;
        show("is_pool_div");
    }else{
        hidd("is_pool_div");
    }

}

                {/literal}
{if $data[$name]=="yes"}
                {literal}
                _is_pool_check();
                {/literal}
{/if}
                </script>
    
{/if}

{/if}
    {/foreach}

<!--{$smarty.template}-->
