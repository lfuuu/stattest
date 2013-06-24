<html><head></head>
<BODY text=#404040 bgColor=#efefef style='padding:0 10 0 10;margin:0 0 0 0;border:0;font-size:12px;font-family:Verdana;color:red'>
{foreach from=$monitoring_bad item=item name=outer}
{if $item.client}
<a href='{$LINK_START}module=clients&id={$item.client}' target=_top style='color:red'>{$item.client}</a> 
{else}
<a href='{$LINK_START}module=routers&id={$item.router}' target=_top style='color:red'>{$item.router}</a> (роутер) 
{/if}

{/foreach}
<script language=javascript>
function monitfunc(){ldelim}
	window.location.href="?module=monitoring&action=top";
{rdelim};
window.setTimeout("monitfunc()",300000);
</script>
</body></html>