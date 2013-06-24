<script>
	alert("Не забудьте установить альбомный формат бумаги");
</script>
<frameset rows="{$rows}">
	{foreach from=$invoices item=i}
	<frame src="view_inv.php?invoice_no={$i.no}&todo=invoice" name="" marginwidth="10" marginheight="10">
	{/foreach}
</frameset>
