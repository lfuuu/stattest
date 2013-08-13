      <H2>Сервера АТС</H2>
{if access('routers_routers','add')}
<a href='{$LINK_START}module=routers&action=server_pbx_add'>Добавить</a><br>
{/if}
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="15%">Название</TD>
          <TD class=header vAlign=bottom width="15%">IP</TD>
          <TD class=header vAlign=bottom width="15%">Тех площадка</TD>
          <TD class=header vAlign=bottom width="15%">&nbsp;</TD>
		</TR>
{foreach from=$ds item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
          <TD><a href='{$LINK_START}module=routers&action=server_pbx_apply&id={$item.id}'>{$item.name}</a></TD>
          <TD>{$item.ip}</TD>
          <TD>{$item.datacenter}</TD>
          <TD>{if $item.count == 0}<a href='{$LINK_START}module=routers&action=server_pbx_apply&dbform_action=delete&dbform[id]={$item.id}'><img class=icon src='{$IMAGES_PATH}icons/delete.gif'>удалить</a>{else}&nbsp;{/if}</TD>
</TR>
{/foreach}
</TBODY></TABLE>
