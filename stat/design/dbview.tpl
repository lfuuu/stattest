<h2>{$dbview_headers}</h2>

{foreach from=$dbview_filters key=key item=group name=outer}
    <b>{$key}</b>:
    {foreach from=$group item=item name=inner}
        {if !$item.selected}
            <a href='{$LINK_START}{$dbview_link_read}{foreach from=$dbview_filters item=g key=gk name=g}&filter[{$smarty.foreach.g.iteration}]={if $gk==$key}{$item.value}{else}{foreach from=$g item=f name=f}{if $f.selected}{$f.value}{/if}{/foreach}{/if}{/foreach}'>{$item.title}</a>
        {else}
            {$item.title}
        {/if}
    {/foreach}
    <br />
{/foreach}

<a href="{$LINK_START}{$dbview_link_edit}">Добавить</a>
<table class="price" cellspacing="2" cellpadding="1" border="0" width="100%">
    {if count($dbview_fieldgroups)}
        <tr>
            {foreach from=$dbview_fieldgroups item=item key=key}
                <td colspan="{$item.0}"{if $item.1} class="header" style="font-weight:bold"{else} style="background:none"{/if}>
                    {$item.1}
                </td>
            {/foreach}
        </tr>
    {/if}
    <tr>
        {foreach from=$dbview_fields item=item key=key}
            <td class="header">{$item}</td>
        {/foreach}
    </tr>
    {foreach from=$dbview_data item=item key=key name=outer}
        <tr class="{if isset($item._tr_class)}{$item._tr_class}{else}{cycle values="even,odd"}{/if}">
            {foreach from=$dbview_fields item=itemF key=keyF name=inner}
                {if $keyF == 'price_include_vat'}
                    {if $item.$keyF == 1}
                        {assign var=value value='вкл. НДС'}
                    {else}
                        {assign var=value value='без НДС'}
                    {/if}
                {else}
                    {assign var=value value=$item.$keyF}
                {/if}

                {if $smarty.foreach.inner.iteration==1}
                    <td>
                        <a href="{$LINK_START}{$dbview_link_edit}&id={$item.id}">{$value}</a>
                    </td>
                {else}
                    <td>{$value}</td>
                {/if}
            {/foreach}
        </tr>
    {/foreach}
</table>