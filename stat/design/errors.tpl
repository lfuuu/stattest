{foreach from=$errors item=item}
<font color=red>[{$item[1]}:{$item[2]}] <b>{$item[0]}</b></font><br>
{/foreach}
{foreach from=$notices item=item}
<font color=red><b>{$item[0]}</b></font><br>
{/foreach}