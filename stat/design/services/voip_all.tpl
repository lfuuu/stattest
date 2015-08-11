<H2>Услуги</H2>
<H3>IP-телефония</H3>
Поиск: <form style='display:inline' action='?' method=get><input type=hidden name=module value=services><input type=hidden name=action value=vo_view><input type=text class=text name=phone value="{$phone}"> <input type=submit class=text value='Искать'></form><br>
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="10%">{sort_link sort=6 text='Клиент' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">{sort_link sort=1 text='Дата' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">{sort_link sort=3 text='Номер телефона' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="10%">{sort_link sort=4 text='Число линий' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<td>&nbsp;</td>
</TR>
{foreach from=$voip_conn item=item name=outer}
<tr>
	<td><a href="/client/view?id={$item.clientid}">{$item.client}</a></td>
	<td><a href="/usage/voip/edit?id={$item.id}" target="_blank">{$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}</a></td>
	<td>{$item.E164}</td>
	<td>{$item.no_of_lines}</td>
	<td>{if ($item.actual)}<a href="{$LINK_START}module=services&action=vo_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif'>отключить</a>{/if}
		</td>
</tr>	
{/foreach}
</tbody>
</table>
</div>
<br><br>
{if access_action('services','vo_add')}<a href='{$LINK_START}module=services&action=vo_add'>Добавить подключение</a>{/if}
