
<frameset rows="{$rows}">
	{foreach from=$bills item=bill}
	<frame src="view.php?bill_no={$bill}" name="" marginwidth="10" marginheight="10">
	{/foreach}
</frameset>
