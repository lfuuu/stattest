{if count($voip_conn) || !isset($is_secondary_output)}
    {if !isset($is_secondary_output)}
        <h2>Услуги</h2>
        <h3>IP-телефония</h3>
        {if access_action('services','vo_add')}
            <a id='vo_add_link' href='{$LINK_START}module=services&action=vo_add'>
                <img class=icon src='{$IMAGES_PATH}icons/phone_add.gif'>
                Добавить телефонный номер
            </a><br>
        {/if}
    {else}
        <h3><a href='?module=services&action=vo_view'>IP-телефония</a></h3>
    {/if}
    
    {if isset($is_secondary_output)}
        <table border=0 width=99%>
            <tr>
                <td width=50%>
                    <a href='{$LINK_START}module=services&action=vo_act' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Выписать&nbsp;акт</a>
                    <a href='{$LINK_START}module=services&action=vo_act&sendmail=1' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Отправить&nbsp;акт</a>
                    {if $has_trunk}
                        <a href='{$LINK_START}module=services&action=vo_act_trunk' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Выписать&nbsp;акт&nbsp;на&nbsp;транк</a>
                    {/if}
                </td>
                <td width=50% align="right" style="{if $client.voip_disabled or $voip_counters.auto_disabled}background-color: #f4a0a0;{else}color: #ccc;{/if}">
                    <b>Телефония:</b>
                    <label>
                        <input type="checkbox" id="voip_disabled" name="voip_disabled" value=1{if $client.voip_disabled} checked{/if}{if !access("clients", "client_type_change")} disabled{/if}> - Выключить телефонию (МГ, МН, Местные мобильные)
                    </label>
                </td>
            </tr>
        </table>
        {if access("clients", "client_type_change")}
            <script>
                optools.service.voip.initVoipDisabledSaver({$client.id});
            </script>
        {/if}
    {/if}

    <table class="table table-condensed">
        {foreach from=$voip_conn item=item name=inner}
            <tr bgcolor="{if $item.status=='working'}{if $item.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
                <td width=1% nowrap>
                    {if access("services_voip", "edit")}
                        <a href="{$PATH_TO_ROOT}pop_services.php?table=usage_voip&id={$item.id}" target="_blank">{$item.id}</a>
                    {else}
                        {$item.id}
                    {/if}
                    <a href='{$LINK_START}module=stats&action=voip&phone={$item.region}_{$item.E164}'><img class=icon src='{$IMAGES_PATH}icons/stats.gif'></a>
                    {if ($item.actual && (access("services_voip", "close") || access("services_voip", "full")))}<a href="{$LINK_START}module=services&action=vo_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif'></a>{/if}
                    <a href='index.php?module=tt&clients_client={$item.client}&service=usage_voip&service_id={$item.id}&action=view_type&type_pk=1&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
                    {if $item.actual_from > '3000-01-01' && access('services_voip', 'del4000')}<a href="./?module=services&action=vo_delete&id={$item.id}"><img src="{$IMAGES_PATH}del2.gif"></a>{/if}
                </td>
                <td width=5% nowrap>{$regions[$item.region].name}</td>
                <td style="font-size: 8pt; width: 15%;">{if $item.vpbx}<div style="padding: 0 15 0 15; color: blue;">Виртуальная АТС</div>{else}{if $item.address}<a href="{$PATH_TO_ROOT}pop_services.php?table=usage_voip&id={$item.id}" target="_blank">{$item.address}</a>{else}<!-- div style='width:150px;text-align:center'>адрес отсутствует</div-->...{/if}{/if}</td>
                <td nowrap><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_voip&id={$item.id}" target="_blank">{$item.actual_from}&nbsp;-&nbsp;{if $item.actual_to < '3000-01-01'}{$item.actual_to}{/if}</a></td>
                <td nowrap>{$item.E164}&nbsp;x&nbsp;{$item.no_of_lines}{if access('services_voip','view_reg')}&nbsp;<a href="./?module=services&action=vo_view&phone={$item.E164}" title="Посмотреть регистрацию">&raquo;</a>{/if}</td>
                <td>
                    {if isset($ats_schema[$item.E164]) && $ats_schema[$item.E164]}
                        {if $ats_schema[$item.E164] == "new"}<span style="color: green;" title="Новая схема">Новая</span>
                        {elseif $ats_schema[$item.E164] == "old"}<span style="color: gray;" title="Старая схема">Старая</span>
                        {/if}
                    {/if}
                </td>
                <td style="font-size: 8pt;">{$item.tarif.name} ({$item.tarif.month_number}-{$item.tarif.month_line})
                    {if $item.tarif.dest_group != 0}
                        / Набор:
                        {if strpos($item.tarif.dest_group, '5') !== false}Моб{/if}
                        {if strpos($item.tarif.dest_group, '1') !== false}МГ{/if}
                        {if strpos($item.tarif.dest_group, '2') !== false}МН{/if}
                        ({$item.tarif.minpayment_group})
                    {/if}
                    {if strpos($item.tarif.dest_group, '5') === false}
                        / Моб {$item.tarif.tarif_local_mob_name} {if $item.tarif.minpayment_local_mob > 0}({$item.tarif.minpayment_local_mob}){/if}
                    {/if}
                    {if strpos($item.tarif.dest_group, '1') === false}
                        / МГ {$item.tarif.tarif_russia_name} {if $item.tarif.minpayment_russia > 0}({$item.tarif.minpayment_russia}){/if}
                    {/if}
                    {if strpos($item.tarif.dest_group, '1') === false}
                        / МГ {$item.tarif.tarif_russia_mob_name}
                    {/if}
                    {if strpos($item.tarif.dest_group, '2') === false}
                        / МН {$item.tarif.tarif_intern_name} {if $item.tarif.minpayment_intern > 0}({$item.tarif.minpayment_intern}){/if}
                    {/if}
                    {if isset($item.permit)}<br><span style="font-size: 7pt;">{$item.permit}</span>{/if}
                </td>
                <td style="font-size: 8pt;">{$allowed_direction[$item.allowed_direction]}</td>
            </tr>
        {/foreach}
    </table>

    {if $is_vo_view && access("services_voip", "send_settings")}
        <div align=right style="padding-right: 55px; font: normal 8pt sans-serif; ">
            <a href="./?module=services&action=vo_settings_send">Выслать настройки</a>
        </div>
    {/if}
{/if}
