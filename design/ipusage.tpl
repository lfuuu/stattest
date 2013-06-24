<form action="?" method=get>
From: <input type=text name=from value="{$tmfrom}"><br>
To: <input type=text name=to value="{$tmto}"><br>
IP: <select name=ip>
{foreach from=$IP key=key item=item}
	<option value="{$key}"{if $key==$ips} selected{/if}>{$key} - {$item[0]} to {$item[1]}</option>
{/foreach}
</select><br>
<input type=submit value="Submit">
</form><br><br><br>
<img src="?image=1&ip={$ips}&from={$tmfrom}&to={$tmto}">
