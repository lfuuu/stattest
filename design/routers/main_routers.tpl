      <H2>Список роутеров</H2>
{if access('routers_routers','add')}
<a href='{$LINK_START}module=routers&action=r_add'>Добавить роутер</a><br>
{/if}
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="15%">Роутер</TD>
          <TD class=header vAlign=bottom width="15%">Телефон</TD>
          <TD class=header vAlign=bottom width="25%">Адрес</TD>
          <TD class=header vAlign=bottom width="20%">IP сети</TD>
          <TD class=header vAlign=bottom width="15%">Номер модема</TD>
          <TD class=header vAlign=bottom width="10%">&nbsp;</TD>
		</TR>
{foreach from=$routers item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
          <TD><a href='{$LINK_START}module=routers&action=r_view&id={$item.router}'>{$item.router}</a></TD>
          <TD>{$item.phone}</TD>
          <TD>{$item.location}</TD>
          <TD>{ipstat net=$item.net}</TD>
          <TD>{$item.adsl_modem_serial}</TD>
          <TD><a href='{$LINK_START}module=routers&action=r_apply&dbaction=delete&keys[router]={$item.router}'>удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
