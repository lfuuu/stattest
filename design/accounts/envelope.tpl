<script>
	alert("Не забудьте установить конверты");
</script>
<frameset rows="{$rows}">
	{foreach from=$clients item=client}
	<frame src="../../index.php?module=clients&id={$client}&action=print&data=envelope" name="" marginwidth="10" marginheight="10">
	{/foreach}
</frameset>

