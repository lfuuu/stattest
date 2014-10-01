{if isset($fixclient) && $fixclient!=""}
	<script language=javascript>var fixclient="{$fixclient_data.client}";</script>
	{if !access('clients','read')}
		<b>{$fixclient_data.client}</b>
		<div class=card>
	{else}
		<table cellspacing=0 cellpadding=3 border=0><tr><td valign=top>
			<b><a href='{$LINK_START}module=clients&id={$fixclient}' style='font-size:17'>{if $fixclient_data.client}{$fixclient_data.client}{else}<font color=red>id=</font>{$fixclient_data.id}{/if}</a> </b>
		</td><td>
			(<b><a href='{$LINK_START}module=clients&unfix=1'>снять</a></B>)
		</td></tr></table>
	    {$fixclient_data.company} 
		<div class=card>Логин: <STRONG>{$authuser.user}</STRONG>&nbsp; 
	{/if}
{else}
	<script language=javascript>var fixclient="";</script>
	Клиент не выбран
	<div class=card>Логин: <STRONG>{$authuser.user}</STRONG>&nbsp; 
{/if}
<a href='{$LINK_START}action=logout'>Logout</a></div>
