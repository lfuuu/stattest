<script language="JavaScript" src="{$PATH_TO_ROOT}js/JsHttpRequest.js"></script> 
<script language="JavaScript" type="text/javascript" src="{$PATH_TO_ROOT}editor/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript" src="{$PATH_TO_ROOT}editor/richtext_compressed.js"></script>


<form name="RTEDemo" action="?" method="post" onsubmit="updateRTEs();return true;">
<input style='width:100%' type=hidden name=module value=clients>
<input style='width:100%' type=hidden name=action value=recontract>
<input type=hidden name=id value='{$client.id}'>
договор &#8470;<input class=text style='width:100' type=text name=contract_no value={$contract.contract_no}>
от <input class=text style='width:100' type=text name=contract_date value={$contract.contract_date}>
комментарий <input class=text style='width:100' type=text name=comment value={$contract.comment}>
<script language=javascript type='text/javascript'>
initRTE("{$PATH_TO_ROOT}editor/images/", "{$PATH_TO_ROOT}editor/", "", true);

var rte1 = new richTextEditor('contract_content');
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
rte1.html = "{$content|escape:"javascript"}";
rte1.toggleSrc = false;
rte1.build();
</script>

<input type=submit value="Сохранить">
</form>
