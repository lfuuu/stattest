{if count($services_conn) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>Collocation подключения</H3>
{else}
<H3><a href='?module=services&action=co_view'>Collocation подключения</a></H3>
{/if}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
{foreach from=$services_conn item=conn name=outer}
<TR><TR><TD colspan="5"></TD></TR>
<TR>
{if isset($show_client)}
	<TD class=header vAlign=bottom width="10%">клиент</TD>
{/if}
	<TD class=header vAlign=bottom width="17%">дата подкл./откл.</TD>
	<TD class=header vAlign=bottom width="12%">узел :: порт</TD>
	<TD class=header vAlign=bottom width="14%">сеть</TD>
	<td class=header valign=bottom width="15%">
			<a href='{$LINK_START}module=tarifs&m=internet&action=edit&id={$conn.data.tarif_id}' title='{if isset($conn.data.tarif.name)}{$conn.data.tarif.name}{/if}'>{if isset($conn.data.tarif.name)}{$conn.data.tarif.mb_month}-{$conn.data.tarif.pay_month}-{$conn.data.tarif.pay_mb}{/if}</a><br>
			{if $conn.data.tarif_old_id}
				<a style='color:#A0A0A0' href='{$LINK_START}module=tarifs&m=internet&action=edit&id={$conn.data.tarif_old_id}' title='{$conn.data.tarif_old.mb_month}-{$conn.data.tarif_old.pay_month}-{$conn.data.tarif_old.pay_mb}'>{$conn.data.tarif_old.name}</a><br>
			{$conn.data.tarif_change}{/if}
	
	</td>
	<td class=header align=right>
		{if ($conn.data.actual)}Активное подключение. {else}Неактивное подключение. {/if}<br>
		<a href="modules/services/pop_services_internet_ports.php?id={$conn.data.id}" target="_blank">редактировать</a>
		<a href="{$LINK_START}module=services&action=co_add2&id={$conn.data.id}">добавить сеть</a>
		<a href="{$LINK_START}module=services&action=co_close&id={$conn.data.id}">отключить</a>
	</td>
</TR>
	{foreach from=$conn.nets item=net name=inner}
	{if $net.actual}<tr bgcolor="#EEE0B9">{else}<tr bgcolor="#fffff5">{/if}
{if isset($show_client)}<td><a href='{$LINK_START}module=clients&id={$conn.data.client}'>{$conn.data.client}</a></td>{/if}
			<td><a href="modules/services/pop_services_internet.php?id={$net.id}" target="_blank">
			{$net.actual_from}/{if !$net.actual}{$net.actual_to}{/if}</a></td>
			<td>{if $net.port=='mgts'}{$conn.data.node}{else}<a href='{$LINK_START}module=routers&id={$net.node}'>{$conn.data.node}</a>{/if}::{$conn.data.port}</td>
			<td>{ipstat net=$net.net}</td>
			<td>{$conn.data.address}</td>
			<td>{if $net.actual}
				<a target="_blank"  href="{$LINK_START}module=services&action=co_act&id={$net.id}">выписать акт</a>
				<a href='{$LINK_START}module=services&action=co_close2&id={$net.id}'>отключить</a>
				{else}
				Сеть отключена. Выписка акта и отключение невозможны.<br>
				{/if}
				<a href='{$LINK_START}module=stats&action=internet&route={$net.net}'>статистика</a>
			</td>
		</tr>	
	{/foreach}
{/foreach}
</tbody>
</table>
</div>
{if !isset($is_secondary_output)}
<br><br>
{if access_action('services','co_add')}<a href='{$LINK_START}module=services&action=co_add'>Добавить подключение</a>{/if}
{/if}
{/if}
