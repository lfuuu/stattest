{if count($services_8800) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>8800</H3>
{if access_action('services','8800_add')}<a href='{$LINK_START}module=services&action=8800_add'>Добавить услугу</a>{/if}
{else}
<H3><a href='?module=services&action=8800_view'>Номер 8800</a></H3>
{/if}
{*if $virtpbx_akt}
<a href='{$LINK_START}module=services&action=virtpbx_act&id={$virtpbx_akt.id}' target="_blank">
    <img class=icon src='{$IMAGES_PATH}icons/act.gif'>Выписать&nbsp;акт</a><br>
{/if*}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">дата</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom>Номер</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom>Описание</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="5%">Стоимость, без НДС</TD>
</TR>
{foreach from=$services_8800 item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_8800&id={$item.id}" target="_blank">{$item.actual_from} - {$item.actual_to}</a>&nbsp;
		<a href='index.php?module=tt&clients_client={$item.client}&service=usage_8800&service_id={$item.id}&action=view_type&type_pk=3&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
    
    </td>
	<td>{$item.number}</td>
	<td>{$item.description}</td>
	<td>{$item.price*1}</td>
</tr>	
{/foreach}
</tbody>
</table>
</div>
{/if}
