{if count($voip_conn) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>IP-телефония</H3>
{if access_action('services','vo_add')}<a id='vo_add_link' href='{$LINK_START}module=services&action=vo_add'><img class=icon src='{$IMAGES_PATH}icons/phone_add.gif'>Добавить телефонный номер</a>
<br>
{/if}
{else}
<H3><a href='?module=services&action=vo_view'>IP-телефония</a></H3>
{/if}
<a href='{$LINK_START}module=services&action=vo_act' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Выписать&nbsp;акт</a>
<a href='{$LINK_START}module=services&action=vo_act&sendmail=1' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Отправить&nbsp;акт</a>
{if $has_trunk}
<a href='{$LINK_START}module=services&action=vo_act_trunk' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Выписать&nbsp;акт&nbsp;на&nbsp;транк</a>
{/if}
<br />
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
{foreach from=$voip_conn item=item name=inner}
<TR bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
<td>
{if access("services_voip", "edit")}
    <a href="{$PATH_TO_ROOT}pop_services.php?table=usage_voip&id={$item.id}" target="_blank">{$item.id}</a>
{else}
    {$item.id}
{/if}
		<a href='{$LINK_START}module=stats&action=voip&phone={$item.region}_{$item.E164}'><img class=icon src='{$IMAGES_PATH}icons/stats.gif'></a>
		{if ($item.actual && (access("services_voip", "close") || access("services_voip", "full")))}<a href="{$LINK_START}module=services&action=vo_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif'></a>{/if}
		<a href='index.php?module=tt&clients_client={$item.client}&service=usage_voip&service_id={$item.id}&action=view_type&type_pk=1&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
        {if $item.actual_from == '2029-01-01' && access('services_voip', 'del2029')}<a href="./?module=services&action=vo_delete&id={$item.id}"><img src="{$IMAGES_PATH}del2.gif"></a>{/if}
	</td>
    <td>{$regions[$item.region].name}</td>
	<td>{if $item.vpbx}<div style="padding: 0 15 0 15; color: blue;">Виртуальная АТС</div>{else}{if $item.address}<a href="{$PATH_TO_ROOT}pop_services.php?table=usage_voip&id={$item.id}" target="_blank">{$item.address}</a>{else}<!-- div style='width:150px;text-align:center'>адрес отсутствует</div-->...{/if}{/if}</td>	<td><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_voip&id={$item.id}" target="_blank">{$item.actual_from} - {if $item.actual_to!='2029-01-01'}{$item.actual_to}{/if}</a></td>
	<td>{$item.E164} x {$item.no_of_lines}{if access('services_voip','view_reg')} <a href="./?module=services&action=vo_view&phone={$item.E164}" title="Посмотреть регистрацию">&raquo;</a>{/if}</td>
	<!--td>{if $item.cl}{$item.cl}{else}&nbsp;{/if}</td-->
	<!--td>{if $item.cl}{if !$item.enable}<img class=icon src='{$IMAGES_PATH}icons/delete.gif'>{else}<img class=icon src='{$IMAGES_PATH}icons/add.gif'>{/if}{else}&nbsp;{/if}</td-->
	<td>{$item.tarif.name} ({$item.tarif.month_number}-{$item.tarif.month_line})
		{if $item.tarif.dest_group != 0}
		/ Набор:
		{if strpos($item.tarif.dest_group, '5') !== false}Моб{/if}
		{if strpos($item.tarif.dest_group, '1') !== false}МГ{/if}
		{if strpos($item.tarif.dest_group, '2') !== false}МН{/if}
		{if strpos($item.tarif.dest_group, '3') !== false}СНГ{/if}
		({$item.tarif.minpayment_group})
		{/if}
		{if strpos($item.tarif.dest_group, '5') === false}
		/ Моб {$item.tarif.tarif_local_mob_name} {if $item.tarif.minpayment_local_mob > 0}({$item.tarif.minpayment_local_mob}){/if}
		{/if}
		{if strpos($item.tarif.dest_group, '1') === false}
		/ МГ {$item.tarif.tarif_russia_name} {if $item.tarif.minpayment_russia > 0}({$item.tarif.minpayment_russia}){/if}
		{/if}
		{if strpos($item.tarif.dest_group, '2') === false}
		/ МН {$item.tarif.tarif_intern_name} {if $item.tarif.minpayment_intern > 0}({$item.tarif.minpayment_intern}){/if}
		{/if}
		{if strpos($item.tarif.dest_group, '3') === false}
		/ СНГ {$item.tarif.tarif_sng_name} {if $item.tarif.minpayment_sng > 0}({$item.tarif.minpayment_sng}){/if}
		{/if}
		{if $item.permit}<br><span style="font-size: 7pt;">{$item.permit}</span>{/if}</td>
</tr>

{/foreach}

{*foreach from=$voip_conn_permit item=item name=inner}
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>{$item.callerid}</td>
<td>{$item.cl}</td>
<td>{if !$item.enable}<img class=icon src='{$IMAGES_PATH}icons/delete.gif'>{else}<img class=icon src='{$IMAGES_PATH}icons/add.gif'>{/if}</td>
<td><span style="font-size: 7pt;">{$item.permit}</span></td>
</tr>
{/foreach*}


</tbody>
</table>
</div>


        {if $is_vo_view && access("services_voip", "send_settings")}
    <div align=right style="padding-right: 55px; font: normal 8pt sans-serif; "><a href="./?module=services&action=vo_settings_send">Выслать настройки</a></div>
    {/if}
{/if}
