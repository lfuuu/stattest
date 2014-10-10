<frameset rows="{$rows}">
{foreach from=$objects item=obj}
	{if $obj.obj!='envelope'}
		<frame src="?module=newaccounts&action=bill_print&bill={$obj.bill_no}&object={$obj.obj}&is_pdf={$is_pdf}" name="" marginwidth="10" marginheight="10">
	{else}
		<frame src="?module=clients&action=print&id={$obj.bill_client}&data=envelope{if $obj.param}&{$obj.param}{/if}&is_pdf={$is_pdf}" name="" marginwidth="10" marginheight="10">
	{/if}
{/foreach}
</frameset>
