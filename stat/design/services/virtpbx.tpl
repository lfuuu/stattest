{if count($services_virtpbx) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>Виртаульная АТС</H3>
{if access_action('services','virtpbx_add')}<a href='{$LINK_START}module=services&action=virtpbx_add'>Добавить услугу</a>{/if}
{else}
<H3><a href='?module=services&action=virtpbx_view'>Виртуальная АТС</a></H3>
{/if}
{if $virtpbx_akt}
<a href='{$LINK_START}module=services&action=virtpbx_act&id={$virtpbx_akt.id}' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Выписать&nbsp;акт</a>
<a href='{$LINK_START}module=services&action=virtpbx_act&id={$virtpbx_akt.id}&sendmail=1' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Отправить&nbsp;акт</a>
{/if}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">дата</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="35%">Описание</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="5%">Стоимость</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">Сервер АТС</TD>
</TR>
{foreach from=$services_virtpbx item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_virtpbx&id={$item.id}" target="_blank">{$item.actual_from} - {if $item.actual_to < '3000-01-01'}{$item.actual_to}{/if}</a>&nbsp;
	<a href="./?module=stats&action=report_vpbx_stat_space&client_id={$fixclient_data.id}"><img class="icon" src="{$IMAGES_PATH}icons/stats.gif"></a>
		<a href='index.php?module=tt&clients_client={$item.client}&service=usage_virtpbx&service_id={$item.id}&action=view_type&type_pk=3&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
    	{if $item.actual_from > '3000-01-01' && access('services_voip', 'del4000')}<a href="./?module=services&action=virtpbx_delete&id={$item.id}"><img src="{$IMAGES_PATH}del2.gif"></a>{/if}
    	
    </td>
	<td>{$item.tarif.description}</td>
	<td>{$item.tarif.price}</td>
	<td>{$item.regionName}</td>
</tr>	
{/foreach}
</tbody>
</table>
</div>
{/if}
