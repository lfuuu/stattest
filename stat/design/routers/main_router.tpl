      <H2>Роутер {$router.router}</H2>
      <H3>Информация о роутере</H3>
Телефон: {$router.phone}<br>
Местонахождение: {$router.location}<br>
Контактное лицо для перезагрузок: {$router.reboot_contact}<br>
IP сети: {$router.net}<br>
Серийный номер ADSL-модема: {$router.adsl_modem_serial}<br>
<a href='{$LINK_START}module=services&action=in_view_routed&router={$router.router}'>Перейти к подключениям</a><br>
{if access('routers_routers','edit')}
<a href='{$LINK_START}module=routers&action=r_edit&router={$router.router}'>Редактировать</a><br>
{/if}
<br>
<h3>Сети</h3>
<table class=price cellSpacing=4 cellPadding=2 border=0 width='60%'>
<tr><td class=header>Сеть</td><td class=header>клиент</td><td class=header>подключение</td></tr>
{foreach from=$nets item=r}
<TR bgcolor="{if $r.active}#EEDCA9{else}#fffff5{/if}">
    <td align=right>{$r.net}</td>
    <td><a href='/client/view?id={$r.clientid}'>{$r.client}</a></td>
    <td><a href='pop_services.php?table=usage_ip_ports&id={$r.id}'>{$r.id}</a></td>
</tr>
{/foreach}
</table>

      <H3>Клиенты</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="15%">{sort_link sort=1 text='Клиент' link='?module=routers&id=' link2=$router.router sort_cur=$sort so_cur=$so}</TD>
          <TD class=header vAlign=bottom width="15%">{sort_link sort=2 text='Интерфейс' link='?module=routers&id=' link2=$router.router sort_cur=$sort so_cur=$so}</TD>
          <TD class=header vAlign=bottom width="20%">{sort_link sort=3 text='Дата/Время' link='?module=routers&id=' link2=$router.router sort_cur=$sort so_cur=$so}</TD>
          <TD class=header vAlign=bottom width="15%">Подсеть</TD>
          <TD class=header vAlign=bottom width="15%">PPP-логины</TD>
          <TD class=header vAlign=bottom width="20%">{sort_link sort=4 text='Адрес' link='?module=routers&id=' link2=$router.router sort_cur=$sort so_cur=$so}</TD>
        </TR>
<?
{foreach from=$router_clients item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
          <TD><a href='/client/view?id={$item.clientid}'>{$item.client_company}</a></TD>
          <TD>{$item.node}::{$item.port}</TD>
          <TD>{$item.actual_from} - {$item.actual_to}</TD>
          <TD>{foreach from=$item.ip_routes item=item_inner name=inner}{ipstat net=$item_inner.net}<br>{/foreach}</TD>
          <TD>{foreach from=$item.ip_ppp item=item_inner name=inner}{$item_inner.login}<br>{/foreach}</TD>
          <TD>{$item.address}</TD>
          </TR>
{/foreach}
</TBODY></TABLE>
