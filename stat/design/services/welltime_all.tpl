

<H2>Услуги</H2>
<H3>WellTime</H3>


<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >id</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Клиент</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Описание</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >дата c</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >дата по</TD>
	<TD style='background-color:#FFFFD8' class=header vAlign=bottom >Количество</TD>
	<TD style='background-color:#FFFFD8' class=header vAlign=bottom >Стоимость</TD>
	<TD style='background-color:#FFFFD8' class=header vAlign=bottom >IP</TD>
	<TD style='background-color:#FFFFD8' class=header vAlign=bottom >Роутер</TD>
</TR>
{foreach from=$services_welltime item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
    <td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_welltime&id={$item.id}" target="_blank">{$item.id}</a>&nbsp;</td>
    <td {if $item.client_color}style='background-color:{$item.client_color};'{/if}><a {if $item.client_color}style="color: black" {/if}href="./?module=clients&id={$item.client}" target="_blank">{$item.client}</a>&nbsp;</td>
    <td>{$item.description}</td>
    <td nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_welltime&id={$item.id}" target="_blank">{$item.actual_from}</a>&nbsp;</td>
    <td nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_welltime&id={$item.id}" target="_blank">{if $item.actual_to < '3000-01-01'}{$item.actual_to}{/if}</a>&nbsp;</td>

	<td>{$item.amount}</td>
	<td>{$item.price}</td>
	<td>{ipstat net=$item.ip data=$item}</td>
	<td>{$item.router}</td>
</tr>
{/foreach}
</tbody>
</table>
</div>
