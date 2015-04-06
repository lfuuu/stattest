<script language="JavaScript" type="text/javascript" src="{$PATH_TO_ROOT}editor/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript" src="{$PATH_TO_ROOT}editor/richtext_compressed.js"></script>

<h2>Договора</h2>
<div id=add_from style="display:none;">
<FORM action="?" method=post>
	<input type=hidden name=module value=tarifs>
	<input type=hidden name=action value=contracts>
	<input type=hidden name=do value=open>
	<input type=hidden name=new value=true>
    <table border=0 width=90%>
    <tr>
    <td>Договор:</td><td>
	<select class=text name=contract_template_group>
    {foreach from=$templates key=k item=item}
        <option value="{$k}"{if $contract_template_group == $k} selected{/if}>{$k}</option>
    {/foreach}
	</select> 
    <input type=text name=contract_template_add>
    <input type=submit value="Создать">
    <input type=button value="Отмена" onclick="toggle(true);">
    </td>
    </tr>
    </table>
    </form>
    </div>

<div id=edit_from style="display:block;">
    <table border=0 width=90%>
    <tr>
    <td>Договор:</td><td>
    <table width=100%><tr><td>
<FORM action="?" method=post onsubmit="updateRTEs();return true;">
	<input type=hidden name=module value=tarifs>
	<input type=hidden name=action value=contracts>
	<input type=hidden name=do value=open>
	<select class=text name=contract_template_group id="contract_template_group" onChange="do_change_template_group(this)">
    {foreach from=$templates key=k item=item}
        <option value="{$k}"{if $contract_template_group == $k} selected{/if}>{$k}</option>
    {/foreach}
	</select> 

	<select class=text name=contract_template id="contract_template">
    {foreach from=$templates[$contract_template_group] item=item}
            <option value="{$item}"{if $contract_template == $item} selected{/if}>{$item}</option>
    {/foreach}
	</select> <input type=submit value="Открыть"></form></td><td><img src="./images/icons/add.gif" align=absmiddle style="cursor: pointer;" onclick="toggle(false);"></td><td align=right>{$info}</td></tr></table>
    </form>
    </td>
    </tr>
{if $is_opened}
    <tr>
    <td colspan=2>
<FORM action="?" method=post name=form1 id=form1>
	<input type=hidden name=module value=tarifs>
	<input type=hidden name=action value=contracts>
	<input type=hidden name=do value=open>
	<input type=hidden name="contract_template_group" value="{$contract_template_group}">
	<input type=hidden name="contract_template" value={$contract_template}>

    <textarea id="text" name="text" style="width: 100%; margin: 0px; height: 600px;">{$contract_body}</textarea>
<script type="text/javascript">
{literal}
$(document).ready(function(){
    tinymce.init({
        selector: "textarea",
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste"
        ],
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
    });
});

{/literal}
</script>

{if false}
<script language=javascript type='text/javascript'>
initRTE("{$PATH_TO_ROOT}editor/images/", "{$PATH_TO_ROOT}editor/", "", true);

var rte1 = new richTextEditor('text');
rte1.width = "100%";
rte1.height = "500";

rte1.cmdFormatBlock = true;
rte1.cmdFontName = true;
rte1.cmdFontSize = true;
rte1.cmdIncreaseFontSize = true;
rte1.cmdDecreaseFontSize = true;

rte1.cmdBold = true;
rte1.cmdItalic = true;
rte1.cmdUnderline = true;
rte1.cmdStrikethrough = true;
rte1.cmdSuperscript = true;
rte1.cmdSubscript = true;

rte1.cmdJustifyLeft = true;
rte1.cmdJustifyCenter = true;
rte1.cmdJustifyRight = true;
rte1.cmdJustifyFull = true;

rte1.cmdInsertHorizontalRule = true;
rte1.cmdInsertOrderedList = true;
rte1.cmdInsertUnorderedList = true;

rte1.cmdOutdent = true;
rte1.cmdIndent = true;
rte1.cmdForeColor = true;
rte1.cmdHiliteColor = true;
rte1.cmdInsertLink = true;
rte1.cmdInsertImage = true;
rte1.cmdInsertSpecialChars = true;
rte1.cmdInsertTable = true;
rte1.cmdSpellcheck = true;

rte1.cmdCut = true;
rte1.cmdCopy = true;
rte1.cmdPaste = true;
rte1.cmdUndo = true;
rte1.cmdRedo = true;
rte1.cmdRemoveFormat = true;
rte1.cmdUnlink = true;


rte1.html = "{$contract_body|escape:"javascript"}";
rte1.toggleSrc = true;
rte1.build();
</script>
{/if}
    </td>
    </tr>
    <tr>
    <td colspan=2 align=right>
    <input type=submit value="Сохранить" name="save_text" style="width: 100%"></td>
</form>
    </tr>
{/if}
    </table>
    </div>
<script>
var tDogovors = new Array();
    {foreach from=$templates key=group item=item}
tDogovors["{$group}"] = new Array();
        {foreach from=$item item=dov}
tDogovors["{$group}"].push('{$dov}');
        {/foreach}
    {/foreach}
{literal}

function do_change_template_group(o){
    var group = o.options[o.selectedIndex].value;

    var oContractTemplate = document.getElementById("contract_template");

    for(i = oContractTemplate.options.length; i >= 1 ;i--){
        oContractTemplate.remove(i-1);
    }

    aIds = tDogovors[group];

    var optNames = new Array();

    if(aIds){
        for(a in aIds){
            id = aIds[a];
            optNames.push([id,id])
        }
    }

    optNames.sort(optSort);

    for(a in optNames) {
        var o = optNames[a];
        createOption(oContractTemplate, o[0], o[1]);
    }
}

function optSort(i, ii)
{
    return i[1] == ii[1] ? 0 : (i[1] > ii[1] ? 1 : -1);
}

function toggle(flag)
{
    document.getElementById("add_from").style.display= flag ? "none":"block" ;
    document.getElementById("edit_from").style.display= !flag ? "none":"block" ;
}
{/literal}

</script>
