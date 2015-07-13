<frameset rows="{$rows}">
{foreach from=$objects item=obj}
    {if $obj.doc_type!=""}
        <frame src="/bill/print?&bill_no={$obj.bill_no}&doc_type={$obj.doc_type}&is_pdf={$is_pdf}" name="" marginwidth="10" marginheight="10" />
	{elseif $obj.obj!='envelope'}
		<frame src="?module=newaccounts&action=bill_print&bill={$obj.bill_no}&object={$obj.obj}&is_pdf={$is_pdf}" name="" marginwidth="10" marginheight="10">
	{else}
		<frame src="/document/print-envelope/?clientId={$obj.bill_client}" name="" marginwidth="10" marginheight="10">
	{/if}
{/foreach}
</frameset>
