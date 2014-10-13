<H2>Номера в резерве</H2>
<TABLE class=price cellSpacing=4 cellPadding=2 width="60%" border=0>
<tr>
    <td style="text-align: center;" class="header" vAlign=bottom rowspan=2>Клиент</td>
    <td style="text-align: center;" class="header" vAlign=bottom rowspan=2>Номер</td>
    <td style="text-align: center;" class="header" vAlign=bottom colspan=2>Дата</td>
    <td style="text-align: center;" class="header" vAlign=bottom rowspan=2>Количество дней<br/>до снятия с резерва</td>
</tr>
<tr>
    <td style="text-align: center;" class="header" vAlign=bottom>Cоздания</td>
    <td style="text-align: center;" class="header" vAlign=bottom>Последнего изменения</td>
</tr>

{if $data}
    {foreach from=$data item="s" name=outer key="k"}
        <tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
            <td>
                <a href="./?module=clients&id={$s->client}">{$s->client}</a>
            </td>
            <td>
                <a target="_blank" href="{$PATH_TO_ROOT}pop_services.php?&table=usage_voip&id={$s->id}">{$s->number}</a>
            </td>
            <td align=right>
                {$s->min_ts|mdate:"d месяца Y г."}
            </td>
            <td align=right>
                {assign var="color" value=""}
                {if "d-m-Y"|date:$s->max_ts == "d-m-Y"|date:$s->min_ts}
                    {assign var="color" value="#C0C0C0"}
                {/if}
                <span {if $color}style="color: {$color};"{/if}>{$s->max_ts|mdate:"d месяца Y г."}</span>
            </td>
            <td align=right>
                {assign var="color" value=""}
                {if $s->diff >= 27 && $s->diff < 30}
                    {assign var="color" value="blue"}
                {elseif $s->diff >= 30}
                    {assign var="color" value="red"}
                {/if}
                <span {if $color}style="color: {$color};"{/if}>{math equation="x-y" x="30" y=$s->diff}</span>
            </td>
        </tr>
    {/foreach}
{else}
    <tr><td colspan="7" style="text-align: center;">Нет информации</td></tr>
{/if}
</table>
