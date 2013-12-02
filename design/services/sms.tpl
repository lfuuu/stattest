{if count($services_sms) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>СМС</H3>
{if access_action('services','sms_add')}<a href='{$LINK_START}module=services&action=sms_add'>Добавить услугу</a>{/if}
{else}
<H3><a href='?module=services&action=sms_view'>СМС</a></H3>
{/if}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">Дата</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom>Тариф</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom>Стоимость, руб. с НДС</TD>
</TR>
{foreach from=$services_sms item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_sms&id={$item.id}" target="_blank">{$item.actual_from} - {$item.actual_to}</a>&nbsp;
		<a href='index.php?module=tt&clients_client={$item.client}&service=usage_sms&service_id={$item.id}&action=view_type&type_pk=3&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
    
    </td>
	<td>{$item.description}</td>
	<td>{$item.per_month_price} / {$item.per_sms_price}</td>
</tr>	
{/foreach}
</tbody>
</table>
</div>
{/if}
