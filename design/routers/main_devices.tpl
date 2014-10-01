{if count($devices) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
      <H2>Клиентские устройства</H2>
{if access('routers_devices','add')}<a href='{$LINK_START}module=routers&action=d_add'><img class=icon src='{$IMAGES_PATH}icons/add.gif'>Добавить устройство</a><br>{/if}
<FORM action="?" method=get id=form name=form style='padding: 0,0,0,0; margin:1,1,1,1'>Фильтрация по IP/номеру/серийнику:
<input type=hidden name=module value=routers><input type=hidden name=action value=d_list>
<input type=text name=search class=text value='{$search}'><input type=submit class=button value='OK'> (<a href='?module=routers&action=d_list'>все</a>)</FORM>

{else}
<H3><a href='?module=routers&action=d_list'>Клиентские устройства</a></H3>
{/if}
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
		<tr>
			<td colspan="{if !isset($is_secondary_output)}8{else}7{/if}">
				Страница:
				{section loop=$pages_count+1 start=1 name=pages_section}
				{if $smarty.section.pages_section.index eq $cur_page}
				<font size="+1" color="blue">{$smarty.section.pages_section.index}</font>
				{else}
				<a href="?module=routers&action=d_list&page={$smarty.section.pages_section.index}">{$smarty.section.pages_section.index}</a>
				{/if}
				{/section}
			</td>
		</tr>
        <TR>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="5%">id</TD>
          {if !isset($is_secondary_output)}
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">Клиент</a></TD>
          {/if}
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">Устройство</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">Серийный номер</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="25%">Дата-время</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">IP адрес / IP_nat</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="25%">номера</TD>
          <TD width="10%">&nbsp;</TD>
		</TR>
{foreach from=$devices item=item name=outer}
<TR bgcolor="{if $item.actual}#EEDCA9{else}#fffff5{/if}">
          <TD nowrap><a href='{$LINK_START}module=routers&action=d_edit&id={$item.id}'>{$item.id}</a>
<a href='{$LINK_START}module=routers&action=d_act&id={$item.id}' title='Бухгалтерский'><img src='images/icons/act.gif' border=0></a>
<a href='{$LINK_START}module=routers&action=d_act&act=2&id={$item.id}' title='Технический'><img src='images/icons/act.gif' border=0></a>
<a href='{$LINK_START}module=routers&action=d_act&act=3&id={$item.id}' title='Возврат'><img src='images/icons/act.gif' border=0></a>
</TD>
          {if !isset($is_secondary_output)}
          <TD><a href="{$LINK_START}module=clients&id={$item.client}">{$item.client}</a></TD>
          {/if}
          <TD><b>{$item.vendor} {$item.model}</b><br>MAC:{$item.mac}
          </TD>
          <TD>{$item.serial}</TD>
          <TD>{$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}</TD>
          <TD>{ipstat net=$item.ip data=$item}{if $item.ip_nat}{ipstat net=$item.ip_nat data=$item}{/if}</TD>
          <TD>{$item.numbers|hl:$search}</TD>
          <TD><a href='{$LINK_START}module=routers&action=d_apply&dbform_action=delete&dbform[id]={$item.id}'><img class=icon src='{$IMAGES_PATH}icons/delete.gif'>удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
{/if}
