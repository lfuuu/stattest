{if count($devices) || !isset($is_secondary_output)}
      <H2>Модели клиентских устройств</H2>
{if access('routers_devices','edit')}<a href='{$LINK_START}module=routers&action=m_add'><img class=icon src='{$IMAGES_PATH}icons/add.gif'>Добавить модель</a><br>{/if}
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="11%">id</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="25%">Производитель</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">Модель</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="20%">Тип</TD>
          <TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="14%">Сумма залога</TD>
          <TD width="10%">&nbsp;</TD>
		</TR>
{foreach from=$models item=item name=outer}
<TR bgcolor="#EEDCA9">
          <TD><a href='{$LINK_START}module=routers&action=m_apply&id={$item.id}'>{$item.id}</a></TD>
          <TD><a href='{$LINK_START}module=routers&action=m_apply&id={$item.id}'>{$item.vendor}</a></td>
          <td><a href='{$LINK_START}module=routers&action=m_apply&id={$item.id}'>{$item.model}</a></td>
          <TD>{$item.type}</TD>
          <TD>{$item.default_deposit_sum}</TD>
          <TD><a href='{$LINK_START}module=routers&action=m_apply&id={$item.id}&dbform_action=delete'><img class=icon src='{$IMAGES_PATH}icons/delete.gif'>удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
{/if}
