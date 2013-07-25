{if count($services_welltime) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>WellTime</H3>
{if access_action('services','welltime_add')}<a href='{$LINK_START}module=services&action=welltime_add'>Добавить услугу</a>{/if}
{else}
<H3><a href='?module=services&action=welltime_view'>WellTime</a></H3>
{/if}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">дата</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="35%">Описание</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="5%">Количество</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="5%">Стоимость</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="10%">IP</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">Роутер</TD>
</TR>
{foreach from=$services_welltime item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_welltime&id={$item.id}" target="_blank">{$item.actual_from} - {$item.actual_to}</a>&nbsp;
		<a href='index.php?module=tt&clients_client={$item.client}&service=usage_welltime&service_id={$item.id}&action=view_type&type_pk=3&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
    
    </td>
	<td>{$item.description}</td>
	<td>{$item.amount}</td>
	<td>{$item.price}</td>
	<td>{ipstat net=$item.ip data=$item}</td>
	<td>{$item.router}</td>
	{*if $item.actual}<a href="{$LINK_START}module=services&action=welltime_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Отключить"></a>{/if*}
</tr>	
{/foreach}
</tbody>
</table>
</div>
{/if}
