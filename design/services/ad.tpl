{if count($adds) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>Доп. услуги лучше заводить <a href='{$LINK_START}module=services&action=ex_add'>нового образца</a></H3>
{if access_action('services','ad_add')}<a href='{$LINK_START}module=services&action=ad_add'>Добавить услугу</a>{/if}
{else}
<H3><a href='?module=services&action=ad_view'>Старые доп.услуги</a></H3>
{/if}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">дата</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="35%">Описание</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="10%">Количество</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">Период</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="10%">Стоимость</TD>
	<td>&nbsp;</td>
</TR>
{foreach from=$adds item=item name=outer}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=bill_monthlyadd&id={$item.id}" target="_blank">{$item.actual_from} - {$item.actual_to}</a></td>
	<td>{$item.description}</td>
	<td>{$item.amount}</td>
	<td>{$item.period_rus}</td>
	<td>{$item.price}</td>
	<td>
	{if $item.actual5d}<a href="{$LINK_START}module=services&action=ad_act&id={$item.id}" target=_blank><img class=icon src='{$IMAGES_PATH}icons/act.gif' alt='Выписать акт'></a>{/if}
	{if ($item.actual)}<a href="{$LINK_START}module=services&action=ad_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Отключить"></a>{/if}
		</td>
</tr>	
{/foreach}
</tbody>
</table>
</div>
{/if}
