      <H2>Статистика</H2>
      <H3>VoIP</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
{if $detality=='call'}
          <TD class=header vAlign=bottom>Id</TD>
          <TD class=header vAlign=bottom>Дата/время</TD>
          {if $phone=='all_regions'}<TD class=header vAlign=bottom>Регион</TD>{/if}
          <TD class=header vAlign=bottom>Исходящий номер</TD>
          <TD class=header vAlign=bottom>Направление</TD>
          <TD class=header vAlign=bottom>Входящий номер</TD>
          <TD class=header vAlign=bottom>Время разговора</TD>
          <TD class=header vAlign=bottom>Стоимость{if !$price_include_vat} (без НДС){/if}</TD>
          <TD class=header vAlign=bottom>Назначение</TD>
          <TD class=header vAlign=bottom>Детализация</TD>
          <TD class=header vAlign=bottom>Местоположение</TD>
{else}
          <TD class=header vAlign=bottom>Дата/время</TD>
          <TD class=header vAlign=bottom>Число звонков</TD>
          <TD class=header vAlign=bottom>Время разговора</TD>
          <TD class=header vAlign=bottom>Стоимость{if !$price_include_vat} (без НДС){/if}</TD>
{/if}
        </TR>

{foreach from=$stats item=item key=key name=outer}
    <TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
{if $detality=='call'}
    <TD style="color:gray">{if isset($item.id)}{$item.id}{/if}</TD>
    <TD>{$item.tsf1}</TD>
    {if $phone=='all_regions'}<TD>{$item.reg_id}</TD>{/if}
    <TD>{if isset($item.src_number)}{$item.src_number}{/if}</TD>
    <TD style="color: {if isset($item.orig) && $item.orig === false}blue;">&darr;&nbsp;входящий{elseif isset($item.orig) && $item.orig === true}green">&uarr;&nbsp;исходящий{else}">{/if}</td>
    <TD>{if isset($item.dst_number)}{$item.dst_number}{/if}</TD>
    <TD><b>{$item.tsf2}</b></TD>
    {if $smarty.foreach.outer.last}
         <TD colspan='2'>{$item.price}</TD>
    {else}
        <TD>{$item.price}</TD>
        <TD>{$item.geo}</TD>
    {/if}
    <td>
        <small>
            {if isset($item.package_minute) && $item.package_minute.minute}<span
                class="package_taken_{$item.package_minute.taken}">{$item.package_minute.name} /
                Минут: {$item.package_minute.minute} / {$item.package_minute.destination}</span>
                <br/>
            {/if}
            {if isset($item.package_price)}<span
                class="package_taken_{$item.package_price.taken}">{$item.package_price.name} /
                Цена: {$item.package_price.price} / {$item.package_price.destination}</span>
                <br/>
            {/if}
            {if isset($item.package_pricelist)}<span
                class="package_taken_{$item.package_pricelist.taken}">{$item.package_pricelist.name} /
                Прайс-лист: {$item.package_pricelist.pricelist}</span>{/if}
            {if isset($item.package_pricelist_nnp)}<span
                class="package_taken_{$item.package_pricelist_nnp.taken}">{$item.package_pricelist_nnp.name} /
                Прайс-лист: {$item.package_pricelist_nnp.pricelist} (rate:{$item.package_pricelist_nnp.rate}
                )</span>{/if}
            {if $item.nnp_pricelist_filter_b_id }
                <br>Направлние v2: <b>{$filtersb[$item.nnp_pricelist_filter_b_id]}</b>
            {/if}
        </small>
    </td>
    <TD>{$item.location_name}</TD>
{else}
    <TD>{if isset($item.account_package_id)}<a href="/uu/account-tariff/edit?id={$item.account_package_id}" target="_blank">{$item.tsf1}</a>{else}{$item.tsf1}{/if}</TD>
    <TD>{$item.cnt}</TD>
    <TD><b>{$item.tsf2}</b></TD>
    <TD>{$item.price}</TD>
{/if}
    </TR>
{/foreach}
</TBODY></TABLE>
