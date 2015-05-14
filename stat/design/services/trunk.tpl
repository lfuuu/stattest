{if count($items) || !isset($is_secondary_output)}
    {if !isset($is_secondary_output)}
        <h2>Услуги</h2>
        <h3>IP-телефония (Транки)</h3>
        {if access_action('services','vo_add')}
            <a id='vo_add_link' href='/usage/trunk/add?clientAccountId={$fixclient_data.id}'>
                <img class=icon src='{$IMAGES_PATH}icons/phone_add.gif'>
                Добавить транк
            </a><br>
        {/if}
    {else}
        <h3><a href='?module=services&action=trunk_view'>IP-телефония (Транки)</a></h3>
    {/if}

    {if isset($is_secondary_output)}
        <a href='{$LINK_START}module=services&action=vo_act' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Выписать&nbsp;акт</a>
        <a href='{$LINK_START}module=services&action=vo_act&sendmail=1' target="_blank"><img class=icon src='{$IMAGES_PATH}icons/act.gif'>Отправить&nbsp;акт</a>
        <br />
    {/if}

    <table class="table table-condensed">
        {foreach from=$items item=item name=inner}
            <tr bgcolor="{if $item.actual}#EEDCA9{else}#fffff5{/if}">
                <td width=1% nowrap>
                    {if access("services_voip", "edit")}
                        <a href="/usage/trunk/edit?id={$item.id}" target="_blank">{$item.id}</a>
                    {else}
                        {$item.id}
                    {/if}
                    <a href='index.php?module=tt&clients_client={$item.client}&service=trunk&service_id={$item.id}&action=view_type&type_pk=1&show_add_form=true'><img class=icon src='{$IMAGES_PATH}icons/tt_new.gif' alt="Создать заявку"></a>
                </td>
                <td width=5% nowrap>{$regions[$item.connection_point_id].name}</td>
                <td nowrap>
                    {if access("services_voip", "edit")}
                        <a href="/usage/trunk/edit?id={$item.id}" target="_blank">{$item.trunk_name}</a>
                    {else}
                        {$bill_trunks[$item.trunk_id]}
                    {/if}
                </td>
                <td nowrap>{if $item.actual_from}<a href="/usage/trunk/edit?id={$item.id}" target="_blank">{$item.actual_from}&nbsp;-&nbsp;{if $item.actual_to<'3000-01-01'}{$item.actual_to}{/if}</a>{/if}</td>
                <td width="100%"></td>
            </tr>
        {/foreach}
    </table>

{/if}
