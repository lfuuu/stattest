

<H2>Услуги</H2>
<H3>Виртуальные АТС</H3>


<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >id</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Клиент</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >Описание</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >дата c</TD>
    <TD style='background-color:#FFFFD8' class=header vAlign=bottom >дата по</TD>
	<TD style='background-color:#FFFFD8' class=header vAlign=bottom >Стоимость</TD>
	<TD style='background-color:#FFFFD8' class=header vAlign=bottom >Сервер АТС</TD>
</TR>
{foreach from=$services_virtpbx item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
    <td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_virtpbx&id={$item.id}" target="_blank">{$item.id}</a>&nbsp;</td>
    <td {if $item.client_color}style='background-color:{$item.client_color};'{/if}><a {if $item.client_color}style="color: black" {/if}href="/client/view?id={$item.client}" target="_blank">{$item.client}</a>&nbsp;</td>
    <td>{$item.tarif.description}</td>
    <td nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_virtpbx&id={$item.id}" target="_blank">{$item.actual_from}</a>&nbsp;</td>
    <td nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_virtpbx&id={$item.id}" target="_blank">{if $item.actual_to < '3000-01-01'}{$item.actual_to}{/if}</a>&nbsp;</td>
	<td>{$item.tarif.price*1.18|round:2} ({$item.tarif.price*1})</td>
	<td>{$item.server_pbx}</td>
</tr>
{/foreach}
</tbody>
</table>
</div>
