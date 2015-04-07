{if isset($internet_suffix) && $internet_suffix=="collocation"}
{assign var=actprefix value=co}
{else}
{assign var=actprefix value=in}
{/if}

{if count($services_conn) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>{if $internet_suffix!="collocation"}Интернет{else}Collocation{/if} подключения</H3>
{else}
<H3><a href='?module=services&action={$actprefix}_view'>{if $internet_suffix!="collocation"}Интернет{else}Collocation{/if} подключения</a></H3>
{/if}

<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0 style='margin-top:-9px'>
<TBODY>
{foreach from=$services_conn item=conn name=outer}

<TR><TD colspan="4">
</TD></TR>

<TR bgcolor="{if $conn.data.status=='working'}{if $conn.data.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
{if isset($show_client)}
	<TD>Клиент <b><a href='{$LINK_START}module=clients&id={$conn.data.client}'>{$conn.data.client}</a></b></td>
{/if}
	<TD width="1%" nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_ip_ports&id={$conn.data.id}" target="_blank"><b>{$conn.data.id}</b></a>
						<a href='{$LINK_START}module=stats&action=internet'><img class=icon src='{$IMAGES_PATH}icons/stats.gif' alt='Статистика'></a>
                        {if $conn.data.actual5d}
						<a href="{$LINK_START}module=services&action=in_act{if $conn.data.port_type == 'GPON'}_pon{/if}&id={$conn.data.id}" target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif' alt='Выписать акт'></a>
						<a href="{$LINK_START}module=services&action=in_act{if $conn.data.port_type == 'GPON'}_pon{/if}&id={$conn.data.id}&sendmail=1" target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif' alt='Отправить акт по почте'></a>
						{/if}
                        {if ($conn.data.actual)}<a href="{$LINK_START}module=services&action={$actprefix}_close&id={$conn.data.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Отключить"></a>{/if}
						<a href='index.php?module=tt&clients_client={$conn.data.client}&service=usage_ip_ports&service_id={$conn.data.id}&action=view_type&type_pk=1&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
	</td>
	<td>{$conn.data.address}</td>
	<td title="Время проверки скорости: {$conn.data.speed_update}" {if isset($conn.data.tarif) && $conn.data.speed_mgts != $conn.data.tarif.adsl_speed} style="color: #c40000;"><b>{$conn.data.speed_mgts}</b> ({$conn.data.tarif.adsl_speed}){else}>{if isset($conn.data.tarif)}{$conn.data.tarif.adsl_speed}{/if}{/if}</td>
	<TD>{$conn.data.actual_from}/{if !$conn.data.actual}{$conn.data.actual_to}{/if}</td>
	<td><b>{$conn.data.port_type}, {if $conn.data.port=='mgts'}{$conn.data.node}{else}<a href='{$LINK_START}module=routers&id={$conn.data.node}'>{$conn.data.node}</a>::{$conn.data.port}{/if}</b></td>
	<td>
	{if isset($conn.data.tarif.name)}<img alt='Текущий тариф' class=icon src='{$IMAGES_PATH}icons/tarif.gif' alt="{$conn.data.tarif.mb_month}-{$conn.data.tarif.pay_month}-{$conn.data.tarif.pay_mb}">
		<span style='color:#0000C0' title='Текущий тариф: {$conn.data.tarif.mb_month}-{$conn.data.tarif.pay_month}-{$conn.data.tarif.pay_mb}'>{$conn.data.tarif.name}{if $conn.data.amount > 1} x {$conn.data.amount}{/if}</span><br>{/if}
	{if isset($conn.data.tarif_previous.name) && $conn.data.tarif_previous.id!=$conn.data.tarif.id}
		<span style='color:#C00000' title='Предыдущий тариф: {$conn.data.tarif_previous.mb_month}-{$conn.data.tarif_previous.pay_month}-{$conn.data.tarif_previous.pay_mb}'>{$conn.data.tarif_previous.name}</span><br>{/if}
	{if isset($conn.data.tarif_next.name)}
		<span style='color:#00C000' title='Следующий тариф (с {$conn.data.tarif_next.date_activation}): {$conn.data.tarif_next.mb_month}-{$conn.data.tarif_next.pay_month}-{$conn.data.tarif_next.pay_mb}'>{$conn.data.tarif_next.name}</span><br>{/if}
		</td>
</TR>

{if count($conn.data.cpe)}
{foreach from=$conn.data.cpe item=cpe name=inner}
<TR bgcolor="{if $cpe.actual}#DCEEA9{else}#fffff5{/if}">
{if $smarty.foreach.inner.iteration==1}
	<TD rowspan={count_rows_func param=$conn.data.cpe start=0} bgcolor=#DCEEA9>
{if access('routers_devices','add')}<a href='{$LINK_START}module=routers&action=d_add'><img class=icon src='{$IMAGES_PATH}icons/add.gif'></a>Создать устройство
{else}<b>Устройства:</b>{/if}
	</TD>
{/if}
<TD align=left colspan=2><a href="{$LINK_START}module=routers&action=d_edit&id={$cpe.id}">{$cpe.vendor} {$cpe.model}</a> <a href="{$LINK_START}module=routers&action=d_act&id={$cpe.id}"><img src="images/icons/act.gif" class=icon></a> ({$cpe.type}, id={$cpe.id})</td>
<TD>{$cpe.actual_from}/{*if !$cpe.actual}{$cpe.actual_to}{/if*}{date_proc in=$cpe.actual_to mode="year_filter" year="4000"}</TD>
<TD>{if $cpe.ip}{ipstat net=$cpe.ip}{else}ip не задан{/if}{if isset($cpe.ip_nat)}<br>{ipstat net=$cpe.ip_nat}{/if}</td>
<TD>{$cpe.numbers}</TD>
</TR>
{/foreach}
{else}
<TR bgcolor="#DCEEA9">
<TD>
{if access('routers_devices','add')}<a href='{$LINK_START}module=routers&action=d_add'><img class=icon src='{$IMAGES_PATH}icons/add.gif'></a>Создать устройство
{else}<b>Устройства:</b>{/if}
</TD>
<TD align=left colspan=3>Клиентское устройство не определено</td>
</TR>
{/if}


{if !count($conn.nets)}
	<TR bgcolor="#fffff5">
	<td colspan=2 bgcolor="#fffff5">
<a href="{$LINK_START}module=services&action=in_add2&id={$conn.data.id}"><img class=icon src='{$IMAGES_PATH}icons/add.gif' alt="Добавить сеть"></a>Создать сеть
	</td>
	<td></td>
	<td></td>
	</tr>
{else}
	{foreach from=$conn.nets item=net name=inner}
	<TR bgcolor="{if $net.actual}#EEDCA9{else}#fffff5{/if}">
	{if $smarty.foreach.inner.iteration==1}
	<td rowspan={count_rows_func param=$conn.nets} bgcolor="{if $conn.data.actual}#EEDCA9{else}#fffff5{/if}">
<a href="{$LINK_START}module=services&action=in_add2&id={$conn.data.id}"><img class=icon src='{$IMAGES_PATH}icons/add.gif' alt="Добавить сеть"></a>Создать сеть
	</td>
	{/if}
	<td colspan=2><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_ip_routes&id={$net.id}" target="_blank">{$net.net}{if $net.nat_net}<br>{$net.nat_net}{/if}</a> (id={$net.id})</td>
	<td>{$net.actual_from}/{if !$net.actual}{$net.actual_to}{/if}</td>
	<td>{ipstat net=$net.net}{if $net.nat_net}{ipstat net=$net.nat_net data=$net}{/if}</td>
	<TD>{$net.comment}</TD>
	</tr>
	{/foreach}
{/if}
{/foreach}
</tbody>
</table>
</div>

{if !isset($is_secondary_output)}
<br><br>
{if $internet_suffix=="collocation"}
{if access_action('services','co_add')}<a href='{$LINK_START}module=services&action={$actprefix}_add'>Добавить подключение</a>{/if}
{else}
{if access_action('services','in_add')}<a href='{$LINK_START}module=services&action={$actprefix}_add'>Добавить подключение</a>{/if}
{/if}

{/if}
{/if}
