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
          <TD><a href='{$LINK_START}module=routers&action=server_pbx_apply&id={$item.id}'>{$item.name}</a>{if $fixclient} &nbsp; <a href="./?module=tt&clients_client={$fixclient}&server_id={$item.id}&action=view_type&type_pk=1&show_add_form=true"><img  src="{$IMAGES_PATH}icons/tt_new.gif" alt="Создать заявку"></a>{/if}</TD>
          <TD>{$item.ip}</TD>
          <TD>{$item.datacenter}</TD>
          <TD><a href='{$LINK_START}module=routers&action=server_pbx_apply&dbform_action=delete&dbform[id]={$item.id}'><img class=icon src='{$IMAGES_PATH}icons/delete.gif'>удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
