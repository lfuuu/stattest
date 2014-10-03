{if count($ppps) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>PPP-логины</H3>
{else}
<H3><a href='?module=services&action=ppp_view'>PPP-логины</a></H3>
{/if}
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">дата</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">логин</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">ip</TD>
	<td onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">
	{if access('services_ppp','activate')}<a href="{$LINK_START}module=services&action=ppp_activateall&value=1"><img class=icon src='{$IMAGES_PATH}icons/enable.gif' alt='Включить все'></a>
	<a href="{$LINK_START}module=services&action=ppp_activateall&value=0"><img class=icon src='{$IMAGES_PATH}icons/disable.gif' alt='Выключить все'></a>{/if}
</td>
</TR>
{foreach from=$ppps item=item name=outer}
<TR bgcolor="{if $item.enabled}#EEDCA9{else}#fffff5{/if}">
{if access('services_mail','edit')}
	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_ip_ppp&id={$item.id}" target="_blank">{$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}</a></td>
	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_ip_ppp&id={$item.id}" target="_blank">{$item.login}</a></td>
{else}
	<td>{$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}</td>
	<td>{$item.login}</td>
{/if}
	<td>{ipstat net=$item.ip}</a></td>
	<td>
		{if access('services_ppp','activate')}<a href="{$LINK_START}module=services&action=ppp_activate&id={$item.id}">{if ($item.enabled)}<img class=icon src='{$IMAGES_PATH}icons/disable.gif' alt='Выключить'>{else}<img class=icon src='{$IMAGES_PATH}icons/enable.gif' alt='Включить'>{/if}</a>{/if}
		{if access('services_ppp','chpass')}<a href="{$LINK_START}module=services&action=ppp_chpass&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/password.gif' alt='Сменить пароль'></a>{/if}
	</td>	
</tr>	
{/foreach}
</tbody>
</table>
{if !isset($is_secondary_output)}
<br><br>
{if access('services_ppp','addnew')}
	{if count($ppps)>0}
		{*<a href='{$PATH_TO_ROOT}ppp_lazy.php?client={$fixclient}&action=add' target="_blank">*}
		<a href='{$LINK_START}module=services&action=ppp_append&client={$fixclient}'><img class=icon src='{$IMAGES_PATH}icons/add.gif' alt='Добавить логин'></a>
	{else}
		{if access('services_ppp','full')}
			<a href='{$LINK_START}module=services&action=ppp_add'>Добавить логин</a>
		{else}
			У вас нет прав на заведение первого ppp-логина.
		{/if}
	{/if}
{/if}<br>

{/if}
{/if}
