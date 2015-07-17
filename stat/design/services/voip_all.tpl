<H2>Услуги</H2>
<H3>IP-телефония</H3>
Поиск:
    <form style="display:inline" action="?" method="GET">
        <input type="hidden" name="module" value="services" />
        <input type="hidden" name="action" value="vo_view" />
        <input type="text" name="phone" class="text" value="{$phone}" />
        <input type="submit" class="text" value="Искать" />
    </form><br />
    <div style="border: 1px;">
        <table class="price" cellspacing="4" cellpadding="2" width="100%" border="0">
            <tbody>
                <tr>
                    <td onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="10%">{sort_link sort=6 text='Клиент' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</td>
                    <td onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">{sort_link sort=1 text='Дата' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</td>
                    <td onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="15%">{sort_link sort=3 text='Номер телефона' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</td>
                    <td onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="10%">{sort_link sort=4 text='Число линий' link='?module=services&action=vo_view&search=' link2=$search sort_cur=$sort so_cur=$so}</td>
                    <td>&nbsp;</td>
                </tr>
                {foreach from=$voip_conn item=item name=outer}
                    <tr>
                        <td>
                            <a href="{$LINK_START}module=clients&id={$item.client}">{$item.client}</a>
                        </td>
                        <td>
                            {if access("services_voip", "edit")}
                                <a href="/usage/voip/edit?id={$item.id}" target="_blank">{$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}</a>
                            {else}
                                {$item.actual_from} - {if !$item.actual}{$item.actual_to}{/if}
                            {/if}
                        </td>
                        <td>{$item.E164}</td>
                        <td>{$item.no_of_lines}</td>
                        <td>
                            {if ($item.actual)}
                                <a href="{$LINK_START}module=services&action=vo_close&id={$item.id}"><img class=icon src='{$IMAGES_PATH}icons/delete.gif'>отключить</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <br /><br />