<h3>Назначения номеров (tech, для автоматического режима)</h3>
<TABLE class=price cellSpacing=2 cellPadding=1 border=0>
{foreach from=$dbform_f_C2U item=o_ports key=o_phone name=outer}
<tr class={cycle values='even,odd'}><td>{$o_phone}</td><td>{foreach from=$o_ports item=v key=i name=inner}{if $i!==0}
	<input type=checkbox value=1 name=dbform[t_C2U][{$o_ports[0]}][{$i}]{if $v==1} checked{/if}>
{/if}{/foreach}</td></tr>
{/foreach}
</table>
