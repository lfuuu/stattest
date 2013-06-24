<H2>Услуги</H2>
<H3>Интернет подключения с непрописанным (или несуществующим) портом</H3>
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>

<TR><TR><TD colspan="5"></TD></TR>
<TR>
	<TD class=header vAlign=bottom width="10%">клиент</TD>
	<TD class=header vAlign=bottom width="17%">дата подкл./откл.</TD>
	<TD class=header vAlign=bottom width="12%">узел :: порт</TD>
	<TD class=header vAlign=bottom width="14%">сеть</TD>
</TR>
	{foreach from=$services_nets item=net name=inner}
		<tr>
			<td><a href='{$LINK_START}module=clients&id={$net.client}'>{$net.client}</a></td>
			<td><a href="modules/services/pop_services_internet.php?id={$net.id}" target="_blank">
			{$net.actual_from}/{$net.actual_to}</a></td>
			<td>{if $net.port=='mgts'}{$net.node}{else}<a href='{$LINK_START}module=routers&id={$net.node}'>{$net.node}</a>{/if}::{$net.port}</td>
			<td>{ipstat net=$net.net}</td>
			<td><a href="create_port.php?client={$net.client}&net_id={$net.id}" target="_blank">создать</a></td>
		</tr>	
	{/foreach}
</tbody>
</table>
</div>
