      <H2>SNMP-устройства</H2>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom>клиент</TD>
          <TD class=header vAlign=bottom>подключение</a></TD>
          <TD class=header vAlign=bottom>порт</TD>
          <TD class=header vAlign=bottom>адрес</TD>
          <TD class=header vAlign=bottom>устройство</TD>
          <TD class=header vAlign=bottom>IP адрес / IP_nat</TD>
          <TD class=header vAlign=bottom>серийный номер</TD>
		</TR>
{foreach from=$devices item=item name=outer}
<TR bgcolor="{if $item.actual}#EEDCA9{else}#fffff5{/if}">
          <TD><a href="/client/view?id={$item.clientid}">{$item.client}</a></TD>
          <TD><a href="pop_services.php?id={$item.id_service}&table=usage_ip_ports">{$item.id_service}</a></TD>
          <TD>{$item.node}::{$item.port_name}</TD>
          <TD>{$item.address}</TD>
          
          <TD><a href='{$LINK_START}module=routers&action=d_edit&id={$item.id}'>{$item.id}</a></TD>
          <TD>{ipstat net=$item.ip data=$item}{if $item.ip_nat}{ipstat net=$item.ip_nat data=$item}{/if}</TD>
          <TD>{$item.serial}</TD>
</TR>
{/foreach}
</TBODY></TABLE>
