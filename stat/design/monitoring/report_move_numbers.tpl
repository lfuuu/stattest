<H2>Перемещаемые услуги</H2>
{if $data}
<H3>Телефония</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr>
        <th class="header" rowspan=2 vAlign=bottom>Номер</th>
        <th class="header" colspan=4 vAlign=bottom>Перемещен с</th>
        <th class="header" colspan=4 vAlign=bottom>Перемещен на</th>
        <th class="header" rowspan=2 vAlign=bottom>Флаг перемещения</th>
</tr>
<tr>
        <th class="header" vAlign=bottom>ID услуги</th>
        <th class="header" vAlign=bottom>Клиент</th>
        <th class="header" vAlign=bottom>Работает с</th>
        <th class="header" vAlign=bottom>Работает по</th>
        
        <th class="header" vAlign=bottom>ID услуги</th>
        <th class="header" vAlign=bottom>Клиент</th>
        <th class="header" vAlign=bottom>Работает с</th>
        <th class="header" vAlign=bottom>Работает по</th>
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
                        <a href="/client/clientview?id={$w->from_client}">{$w->from_client}</a>
                </td>
                <td align="right">
                        {$w->from_actual_from|mdate:"d месяца Y г"}
                </td>
                <td align="right">
                        {if "3000-01-01" < "Y-m-d"|date:$w->from_actual_to}
                                ---
                        {else}
                                {$w->from_actual_to|mdate:"d месяца Y г"}
                        {/if}
                </td>
                <td>
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_voip&id={$w->to_id}">{$w->to_id}</a>
                </td>
                <td>
                        <a href="/client/clientview?id={$w->to_client}">{$w->to_client}</a>
                </td>
                <td align="right">
                        {$w->to_actual_from|mdate:"d месяца Y г"}
                </td>
                <td align="right">
                        {if "3000-01-01" < "Y-m-d"|date:$w->to_actual_to}
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
                
        </tr>
{/foreach}
</table>
{/if}
{if $vpbx_data}
<H3>ВАТС (помеченные как перемещенные)</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr>
        <th class="header" colspan=4 vAlign=bottom>Перемещен с</th>
        <th class="header" rowspan=2>&nbsp;</th>
        <th class="header" colspan=4 vAlign=bottom>Перемещен на</th>
</tr>
<tr>
        <th class="header" vAlign=bottom>ID услуги</th>
        <th class="header" vAlign=bottom>Клиент</th>
        <th class="header" vAlign=bottom>Работает с</th>
        <th class="header" vAlign=bottom>Работает по</th>
        
        
        <th class="header" vAlign=bottom>ID услуги</th>
        <th class="header" vAlign=bottom>Клиент</th>
        <th class="header" vAlign=bottom>Работает с</th>
        <th class="header" vAlign=bottom>Работает по</th>
</tr>
{foreach from=$vpbx_data item="w" key="k" name="week"}
        {if !$day || $day != $w->from_actual_to}
            <tr style="background-color: #CCFF99;">
                <td colspan=9 align=center>
                    {assign var="day" value=$w->from_actual_to}
                    Перемещение с <b>{$w->from_actual_to|mdate:"d месяца Y г"}</b> на <b>{$w->to_actual_from|mdate:"d месяца Y г"}</b>
                </td>
            </tr>
        {/if}
        <tr class={if $smarty.foreach.week.iteration%2==1}even{else}odd{/if}>
                <td>
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_virtpbx&id={$w->from_id}">{$w->from_id}</a>
                </td>
                <td>
                        <a href="/client/clientview?id={$w->from_client}">{$w->from_client}</a>
                </td>
                <td align="right">
                        {$w->from_actual_from|mdate:"d месяца Y г"}
                </td>
                <td align="right">
                        {$w->from_actual_to|mdate:"d месяца Y г"}
                </td>
                <td style="background-color: #CCFF99; text-align: center; vertical-align: middle;">&rArr;</td>
                <td>
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_virtpbx&id={$w->to_id}">{$w->to_id}</a>
                </td>
                <td>
                        <a href="/client/clientview?id={$w->to_client}">{$w->to_client}</a>
                </td>
                <td align="right">
                        {$w->to_actual_from|mdate:"d месяца Y г"}
                </td>
                <td align="right">
                        {if "3000-01-01" < "Y-m-d"|date:$w->to_actual_to}
                                ---
                        {else}
                                {$w->to_actual_to|mdate:"d месяца Y г"}
                        {/if}
                </td>
        </tr>
{/foreach}
</table>
{/if}

{if $possible_vpbx_data}
<H3>ВАТС (с возможностью перемещения)</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr>
        <th class="header" colspan=4 vAlign=bottom>Перемещен с</th>
        <th class="header" rowspan=2>&nbsp;</th>
        <th class="header" colspan=4 vAlign=bottom>Перемещен на</th>
</tr>
<tr>
        <th class="header" vAlign=bottom>ID услуги</th>
        <th class="header" vAlign=bottom>Клиент</th>
        <th class="header" vAlign=bottom>Работает с</th>
        <th class="header" vAlign=bottom>Работает по</th>
        
        
        <th class="header" vAlign=bottom>ID услуги</th>
        <th class="header" vAlign=bottom>Клиент</th>
        <th class="header" vAlign=bottom>Работает с</th>
        <th class="header" vAlign=bottom>Работает по</th>
</tr>
{assign var="day" value=""}
{assign var="id" value=""}
{assign var="iteration" value=0}
{foreach from=$possible_vpbx_data item="w" key="k" name="week"}

            <tr style="background-color: #FFFF99;">
                <td colspan=9 align=center>
                    Перемещение с <b>{$k|mdate:"d месяца Y г"}</b> на <b>{$w.date_to|mdate:"d месяца Y г"}</b>
                </td>
            </tr>
            <tr class={if $smarty.foreach.week.iteration%2==1}even{else}odd{/if}>
                <td>
                    {foreach from=$w.from item=v name=from}
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_virtpbx&id={$v->from_id}">{$v->from_id}</a>
                        {if !$smarty.foreach.from.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
                <td>
                    {foreach from=$w.from item=v name=from}
                        <a href="/client/clientview?id={$v->from_client}">{$v->from_client}</a>
                        {if !$smarty.foreach.from.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
                <td align=right>
                    {foreach from=$w.from item=v name=from}
                        {$v->from_actual_from|mdate:"d месяца Y г"}
                        {if !$smarty.foreach.from.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
                <td align=right>
                    {foreach from=$w.from item=v name=from}
                        {$v->from_actual_to|mdate:"d месяца Y г"}
                        {if !$smarty.foreach.from.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
                
                <td style="background-color: #FFFF99; text-align: center; vertical-align: middle;">|</td>
                
                <td>
                    {foreach from=$w.to item=v name=to}
                        <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_virtpbx&id={$v->to_id}">{$v->to_id}</a>
                        {if !$smarty.foreach.to.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
                <td>
                    {foreach from=$w.to item=v name=to}
                        <a href="/client/clientview?id={$v->to_client}">{$v->to_client}</a>
                        {if !$smarty.foreach.to.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
                <td align=right>
                    {foreach from=$w.to item=v name=to}
                        {$v->to_actual_from|mdate:"d месяца Y г"}
                        {if !$smarty.foreach.to.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
                <td align=right>
                    {foreach from=$w.to item=v name=to}
                        {if "3000-01-01" < "Y-m-d"|date:$v->to_actual_to}
                                ---
                        {else}
                                {$w->to_actual_to|mdate:"d месяца Y г"}
                        {/if}
                        {if !$smarty.foreach.to.last}
                            <br/>
                        {/if}
                    {/foreach}
                </td>
            </tr>
{/foreach}
</table>
{/if}

