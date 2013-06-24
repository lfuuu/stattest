<script>
var tGroup = new Array();
var ids = new Array();
{if isset($tarif_group_data)}
{foreach from=$tarif_group_data item=i}
tGroup["{$i.code}"] = "{$i.ids}".split(",");
{/foreach}

{foreach from=$tarif_data key=k item=i}
ids["{$k}"] = "{$i}";
{/foreach}
{/if}
	var loading = true;
	form_usage_extra_get({if isset($tarif_real_id)}{$tarif_real_id}{/if});
	document.getElementById('tr_param_value').style.display='none';
	document.getElementById('tr_amount').style.display='none';
	document.getElementById('tr_amount').value='1';
</script>
