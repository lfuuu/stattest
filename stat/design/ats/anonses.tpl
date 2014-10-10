{include file="widget/panel.tpl"}
<!--h2>{$title}</h2-->
<script src="js/JsHttpRequest.js"></script>
<script src="js/configTableDelete.js"></script>
<link title="default" href="css/edit.css" type="text/css" rel="stylesheet">
<form method="POST" action="" id="edit_form" enctype="multipart/form-data">
<INPUT type="hidden" name="id" id="id" value="{$data.id}">
<INPUT type="hidden" name="do" value="set">
<INPUT type="hidden" name="module" id="module" value="{$module}">

<table id="edit_table">
	<TR><TD colspan=2 align=center>&nbsp;<b><font style="color: #c40000;">{$msg}</font></b></td></TR>
	<TR><TD class="td_r">{$locale->anonses_name}:</TD><TD><INPUT type="text" name="name" value="{$data.name}"></td></TR>
	<TR><TD class="td_r">{$locale->anonses_file}<div class=hint>{$locale->anonses_hint_file}</div>:</TD><TD><INPUT type="file" name="upfile" style="height: 23px;">&nbsp;&nbsp;{$uplink}</td></TR>
{if $data.sysfile}
	<TR><TD class="td_r">{$locale->anonses_sys_file}<div class=hint>{$locale->anonses_sys_hint}</div>:</TD><TD><input type=checkbox name=use value="sysfile">&nbsp;&nbsp;{$uplink2}</td></TR>
{/if}
</table>

<hr>
</form>
{include file="save_btn.tpl"}
<script language="JavaScript" src="js/hint.js"></script>
<!--{$smarty.template}-->