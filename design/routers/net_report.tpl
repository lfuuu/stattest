<table cellspacing=0 cellpadding=2 border=1 valign=top>
<tr><td>отключенные</td><td>тех.отказ</td><td>новые</td><td>особые</td></tr>
<tr>
<td valign=top>
{foreach from=$data.off item=item key=key}
{$item[1]|date_format:"%d-%m-%Y"} - {$key}/{$item[0]}<br>
{/foreach}
</td>

<td valign=top>
{foreach from=$data.tech item=item key=key}
{$key}/{$item[0]}<br>
{/foreach}
</td>

<td valign=top>
{foreach from=$data.new item=item key=key}
{$key}/{$item[0]}<br>
{/foreach}
</td>

<td valign=top>
{foreach from=$data.special item=item key=key}
{$key}/{$item[0]}<br>
{/foreach}
</td>

</tr></table>
