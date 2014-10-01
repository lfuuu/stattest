<H3>IP-телефония</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
{foreach from=$voip_conn item=item name=inner}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
	<td>
		<a href='{$LINK_START}module=stats&action=voip&phone={$item.E164}'><img class=icon src='{$IMAGES_PATH}icons/stats.gif' title='Статистика'></a>
		{if ($item.actual)}<a href="{$LINK_START}module=phone&action=voip_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif'></a>{/if}
	</td>
	<td>
		<a href='{$LINK_START}module=phone&action=voip_edit&id={$item.id}'><b>{$item.E164}</b></a> &middot; 
		{$item.no_of_lines} лин{$item.no_of_lines|rus_fin:'ия':'ии':'ий'}
	</td>
	<td>{$item.actual_from} - {if $item.actual_to=='2029-01-01'}бессрочно{else}{$item.actual_to}{/if}</td>
	<td>{$item.tarif.name}</td>
</tr>

{/foreach}
</tbody>
</table>
<br>{if $voip_access && access('phone','voip_edit')}<a href='{$LINK_START}module=phone&action=voip_edit'><img class=icon src='{$IMAGES_PATH}icons/phone_add.gif'>Создать ещё подключение</a><br>{/if}
