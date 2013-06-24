{if count($mails) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>E-Mail</H3>
{if access_action('services','em_add')}<a href='{$LINK_START}module=services&action=em_add'>Добавить ящик</a><br>{/if}
{else}
<H3><a href='?module=services&action=em_view'>E-Mail</a></H3>
{/if}
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD class=header vAlign=bottom width="20%" rowspan=2>дата</TD>
	<TD class=header vAlign=bottom width="15%" rowspan=2>ящик</TD>
	<TD class=header vAlign=bottom width="10%" rowspan=2>размер</TD>
	<TD class=header vAlign=bottom style='text-align:center' width="24%" colspan=3>настройки спам-фильтра</TD>
	<td rowspan=2>&nbsp;</td>
</TR>	
<TR>
<Td class=header valign=top width=8%>фильтровать?</TD>
<Td class=header valign=top width=8%>помечать</TD>
<Td class=header valign=top width=8%>удалять</TD>

{foreach from=$mails item=item name=outer}
<TR{if !$item.actual} style="color:#C0C0C0"{/if}>
{if access('services_mail','edit')}
	<td>{$item.actual_from|date_format:"%Y-%m-%d"} - {if !$item.actual}{$item.actual_to|date_format:"%Y-%m-%d"}{/if}</td>
	<td><a{if !$item.actual} style='color:#808080'{/if} href="{$PATH_TO_ROOT}pop_services.php?id={$item.id}&table=emails" target="_blank">{$item.local_part}@{$item.domain}</a></td>
	<td>{$item.box_size} / {$item.box_quota}</td>
{else}
	<td>{$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}</td>
	<td>{$item.local_part}@{$item.domain}</td>
	<td>{$item.box_size} / {$item.box_quota}</td>
{/if}
	<td>
	{if access_action('services','em_whitelist_toggle') && $item.actual}
		{if $item.count_filters || ($item.spam_act=="pass")}
			<input type=checkbox class=text onclick='window.location.href="{$LINK_START}module=services&action=em_whitelist_toggle&mode=1&id={$item.id}"'>
		{else}
			<input type=checkbox class=text onclick='window.location.href="{$LINK_START}module=services&action=em_whitelist_toggle&mode=0&id={$item.id}"' checked>
		{/if}
	{else}{if $item.count_filters || ($item.spam_act=="pass")}&#8211;{else}+{/if}{/if}</td>
	<td>
		{if $item.count_filters || ($item.spam_act=="pass") || !$item.actual}
			<input name=radio{$item.id} type=radio disabled class=text>
		{else}
			<input name=radio{$item.id} {if ($item.spam_act=="mark")}checked {/if}type=radio class=text onclick='window.location.href="{$LINK_START}module=services&action=em_whitelist_toggle&mode=1&id={$item.id}";'>
		{/if}
	</td>
	<td>
		{if $item.count_filters || ($item.spam_act=="pass") || !$item.actual}
			<input name=radio{$item.id} type=radio disabled class=text>
		{else}
			<input name=radio{$item.id} {if ($item.spam_act=="discard")}checked {/if}type=radio class=text onclick='window.location.href="{$LINK_START}module=services&action=em_whitelist_toggle&mode=2&id={$item.id}";'>
		{/if}

	</td>
	<td>
		{if access('services_mail','activate')}<a href="{$LINK_START}module=services&action=em_activate&id={$item.id}" onclick='javascript:return(confirm("Вы уверены?"))'>{if ($item.actual)}деактивировать{else}активировать{/if}</a>{/if}
{if ($item.actual)}
		{if access('services_mail','chpass')}<a href="{$LINK_START}module=services&action=em_chpass&id={$item.id}">сменить пароль</a>{/if}

{/if}
		&nbsp;</td>
</tr>
{/foreach}
</tbody>
</table>
{if count($mailservers)}
<H3>Виртуальные почтовые сервера</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width=50% border=0>
<TBODY>
<TR>
	<TD class=header vAlign=bottom width="40%">дата</TD>
	<TD class=header vAlign=bottom width="40%">стоимость</TD>
	<td>&nbsp;</td>
</TR>
{foreach from=$mailservers item=item name=outer}
<tr>
	<td><a href="{$PATH_TO_ROOT}pop_services_ad.php?id={$item.id}" target="_blank">{$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}</a></td>
	<td><a href="{$PATH_TO_ROOT}pop_services_ad.php?id={$item.id}" target="_blank">{$item.price}</a></td>
	<td>{if $item.enabled}+{else}&nbsp;{/if}</td>
	<td><a href="{$LINK_START}module=services&action=ad_close&id={$item.id}">отключить</a>
		<a href="{$LINK_START}module=services&action=ad_activate&id={$item.id}">{if ($item.actual)}заблокировать{else}разблокировать{/if}</a>
		</td>
</tr>	
{/foreach}
</tbody>
</table>
{/if}
{if !isset($is_secondary_output)}
<br><br>
{if access_action('services','em_whitelist')}<br><a href='{$LINK_START}module=services&action=em_whitelist'>Настройка списка адресов, письма с которых Вы не считаете спамом</a><br>{/if}
{/if}
{/if}
