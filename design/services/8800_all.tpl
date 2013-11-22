

<H2>Услуги</H2>
<H3>8800</H3>

<form action="./?module=services&action=8800_view" id="form_filter_8800" method="post">
<table>
<TR><TD>Менеджер</TD><TD>
<select name='filter_manager' onchange="$('#form_filter_8800').submit();"><option value=''>(без фильтра)</option>{foreach from=$f_manager item=r}<option value='{$r.user}'{if $r.user==$filter_manager} selected{/if}>{$r.name} ({$r.user})</option>{/foreach}</select>
</td></tr>
</table>
</form>
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
</TR>
{foreach from=$services_8800 item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
    <td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_8800&id={$item.id}" target="_blank">{$item.id}</a>&nbsp;</td>
    <td {if $item.client_color}style='background-color:{$item.client_color};'{/if}><a {if $item.client_color}style="color: black" {/if}href="./?module=clients&id={$item.client}" target="_blank">{$item.client}</a>&nbsp;</td>
    <td>{$item.description}</td>
    <td nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_8800&id={$item.id}" target="_blank">{$item.actual_from}</a>&nbsp;</td>
    <td nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_8800&id={$item.id}" target="_blank">{if $item.actual_to != '2029-01-01'}{$item.actual_to}{/if}</a>&nbsp;</td>
	<td>{$item.price*1.18|round:2} ({$item.price*1})</td>
</tr>
{/foreach}
</tbody>
</table>
</div>
