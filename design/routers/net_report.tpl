<table cellspacing=0 cellpadding=2 border=1 valign=top>
<tr><td rowspan=2>отключенные</td><td rowspan=2>тех.отказ</td><td rowspan=2>новые</td><td rowspan=2>особые</td><td colspan=3>GPON резерв</td></tr>
<tr><td>клиент</td><td>Дата</td><td>Сeть</td></tr>
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

<td valign=top>
{foreach from=$data.gpon item=item key=key}
<a href="./?module=clients&id={$item[4]|urlencode}">{$item[4]|htmlentities}</a>&nbsp;({$item[5]})</br>
{/foreach}
</td>

<td valign=top>
{foreach from=$data.gpon item=item key=key}
{$item[1]|date_format:"%d-%m-%Y"}</br>
{/foreach}
</td>

<td valign=top>
{foreach from=$data.gpon item=item key=key}
{$key}/{$item[0]}</br>
{/foreach}
</td>

</tr></table>
