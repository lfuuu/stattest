<H2>Перемещаемые услуги</H2>
<H3>Телефония</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr>
        <td class="header" rowspan=2 vAlign=bottom>Номер</td>
        <td class="header" colspan=4 vAlign=bottom>Перемещен с</td>
        <td class="header" colspan=4 vAlign=bottom>Перемещен на</td>
        <td class="header" rowspan=2 vAlign=bottom>Флаг перемещения</td>
</tr>
<tr>
        <td class="header" vAlign=bottom>ID услуги</td>
        <td class="header" vAlign=bottom>Клиент</td>
        <td class="header" vAlign=bottom>Работает с</td>
        <td class="header" vAlign=bottom>Работает по</td>
        
        <td class="header" vAlign=bottom>ID услуги</td>
        <td class="header" vAlign=bottom>Клиент</td>
        <td class="header" vAlign=bottom>Работает с</td>
        <td class="header" vAlign=bottom>Работает по</td>
</tr>
{foreach from=$data item="w" key="k" name="week"}
        <tr class={if $smarty.foreach.week.iteration%2==0}even{else}odd{/if}>
                <td>
                        {$w->number}
                </td>
                <td>
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_voip&id={$w->from_id}">{$w->from_id}</a>
                </td>
                <td>
                        <a href="?module=clients&id={$w->from_client}">{$w->from_client}</a>
                </td>
                <td align="right">
                        {$w->from_actual_from|mdate:"d месяца Y г"}
                </td>
                <td align="right">
                        {if "2029-01-01" == "Y-m-d"|date:$w->from_actual_to}
                                ---
                        {else}
                                {$w->from_actual_to|mdate:"d месяца Y г"}
                        {/if}
                </td>
                <td>
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_voip&id={$w->to_id}">{$w->to_id}</a>
                </td>
                <td>
                        <a href="?module=clients&id={$w->to_client}">{$w->to_client}</a>
                </td>
                <td align="right">
                        {$w->to_actual_from|mdate:"d месяца Y г"}
                </td>
                <td align="right">
                        {if "2029-01-01" == "Y-m-d"|date:$w->to_actual_to}
                                ---
                        {else}
                                {$w->to_actual_to|mdate:"d месяца Y г"}
                        {/if}
                </td>
                <td>
                        {if $w->is_moved}
                                <span style="color: green;">Да{if $w->is_moved_with_pbx}, вместе с АТС{/if}</span>
                        {else}
                                Нет
                        {/if}
                </td>
                
        </td>
{/foreach}
</table>
<H3>ВАТС</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr>
        <td class="header" colspan=4 vAlign=bottom>Перемещен с</td>
        <td class="header" colspan=4 vAlign=bottom>Перемещен на</td>
        <td class="header" rowspan=2 vAlign=bottom>Флаг перемещения</td>
</tr>
<tr>
        <td class="header" vAlign=bottom>ID услуги</td>
        <td class="header" vAlign=bottom>Клиент</td>
        <td class="header" vAlign=bottom>Работает с</td>
        <td class="header" vAlign=bottom>Работает по</td>
        
        <td class="header" vAlign=bottom>ID услуги</td>
        <td class="header" vAlign=bottom>Клиент</td>
        <td class="header" vAlign=bottom>Работает с</td>
        <td class="header" vAlign=bottom>Работает по</td>
</tr>
{foreach from=$vpbx_data item="w" key="k" name="week"}
        <tr class={if $smarty.foreach.week.iteration%2==0}even{else}odd{/if}>
                <td>
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_virtpbx&id={$w->from_id}">{$w->from_id}</a>
                </td>
                <td>
                        <a href="?module=clients&id={$w->from_client}">{$w->from_client}</a>
                </td>
                <td align="right">
                        {$w->from_actual_from|mdate:"d месяца Y г"}
                </td align="right">
                <td>
                        {if "2029-01-01" == "Y-m-d"|date:$w->from_actual_to}
                                ---
                        {else}
                                {$w->from_actual_to|mdate:"d месяца Y г"}
                        {/if}
                </td>
                <td>
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_virtpbx&id={$w->to_id}">{$w->to_id}</a>
                </td>
                <td>
                        <a href="?module=clients&id={$w->to_client}">{$w->to_client}</a>
                </td>
                <td align="right">
                        {$w->to_actual_from|mdate:"d месяца Y г"}
                </td>
                <td align="right">
                        {if "2029-01-01" == "Y-m-d"|date:$w->to_actual_to}
                                ---
                        {else}
                                {$w->to_actual_to|mdate:"d месяца Y г"}
                        {/if}
                </td>
                <td>
                        {if $w->is_moved}
                                <span style="color: green;">Да</span>
                        {else}
                                Нет
                        {/if}
                </td>
                
        </td>
{/foreach}
</table>

