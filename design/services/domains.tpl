{if count($domains) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>Доменные имена</H3>
{if access_action('services','dn_add')}<a href='{$LINK_START}module=services&action=dn_add'><img class=icon src='{$IMAGES_PATH}icons/add.gif'>Добавить доменное имя</a>{/if}
{else}
<H3><a href='?module=services&action=dn_view'>Доменные имена</a></H3>
{/if}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<tD width=4%>&nbsp;</td>
{if !$fixclient}
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="18%">{sort_link sort=4 text='Клиент' link='?module=services&action=dn_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
{/if}
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="18%">{sort_link sort=5 text='Дата' link='?module=services&action=dn_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="18%">{sort_link sort=1 text='Домен' link='?module=services&action=dn_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="14%">{sort_link sort=2 text='primary_mx' link='?module=services&action=dn_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="14%">{sort_link sort=3 text='paid_till' link='?module=services&action=dn_view&search=' link2=$search sort_cur=$sort so_cur=$so}</TD>
	<td>&nbsp;</td>
</TR>
{foreach from=$domains item=item name=outer}
<TR class={cycle values='even,odd'}>
	<td>{$smarty.foreach.outer.iteration}</td>
{if !$fixclient}
	<td><a href="{$PATH_TO_ROOT}pop_services.php?id={$item.id}&table=domains" target="_blank">{$item.client}</a></td>
{/if}
	<td><a href="{$PATH_TO_ROOT}pop_services.php?id={$item.id}&table=domains" target="_blank">{$item.actual_from|date_format:"%Y-%m-%d"} - {if !$item.actual}{$item.actual_to|date_format:"%Y-%m-%d"}{/if}</a></td>
	<td><a href="{$PATH_TO_ROOT}pop_services.php?id={$item.id}&table=domains" target="_blank">{$item.domain}</a></td>
	<td><a href="{$PATH_TO_ROOT}pop_services.php?id={$item.id}&table=domains" target="_blank">{$item.primary_mx}</a></td>
	<td><a href="{$PATH_TO_ROOT}pop_services.php?id={$item.id}&table=domains" target="_blank">{$item.paid_till}</a></td>
	<td>{if ($item.actual)}<a href="{$LINK_START}module=services&action=dn_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif' alt='Отключить'></a>{/if}
		<a href='index.php?module=tt&clients_client={$item.client}&service=domains&service_id={$item.id}&action=view_type&type_pk=1&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="яНГДЮРЭ ГЮЪБЙС"></a>
		</td>
</tr>	
{/foreach}
</tbody>
</table>
</div>
{/if}
