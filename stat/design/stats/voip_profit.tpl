      <h2>Статистика</h2>
      <h3>VoIP</h3>
      <table class="price" cellspacing="4" cellpadding="2" width="100%" border="0">
        <tr>
{if $type=='call'}
          <td vAlign="bottom" class="header">Id</td>
          <td vAlign="bottom" class="header">Дата/время</td>
          {if $phone=='all_regions'}<td vAlign="bottom" class="header">Регион</td>{/if}
          <td vAlign="bottom" class="header">Направление</td>
          <td vAlign="bottom" class="header">A-номер</td>
          <td vAlign="bottom" class="header">Страна, город</td>
          <td vAlign="bottom" class="header">Оператор</td>
          <td vAlign="bottom" class="header">B-номер</td>
          <td vAlign="bottom" class="header">Страна, город</td>
          <td vAlign="bottom" class="header">Оператор</td>
          <td vAlign="bottom" class="header-center">Время клиента</td>
          <td vAlign="bottom" class="header-center">Время оператора</td>
          <td vAlign="bottom" class="header-center">
              Себестоимость{if !$priceIncludeVat}<br />(без НДС){/if}
          </td>
          <td vAlign="bottom" class="header">прайслист (цена)</td>
          <td vAlign="bottom" class="header-center">
              Стоимость{if !$priceIncludeVat}<br />(без НДС){/if}
          </td>
          <td vAlign="bottom" class="header">прайслист (цена)</td>
          <td vAlign="bottom" class="header-center">
              Маржа{if !$priceIncludeVat}<br />(без НДС){/if}
          </td>
{else}
          <td vAlign="bottom" class="header">Дата/время</td>
          <td vAlign="bottom" class="header-center">Число звонков</td>
          <td vAlign="bottom" class="header-center">Время клиента</td>
          <td vAlign="bottom" class="header-center">Время оператора</td>

          <td vAlign="bottom" class="header-center">Себестоимость(без НДС)</td>
          <td vAlign="bottom" class="header-center">Себестоимость(c НДС)</td>

          <td vAlign="bottom" class="header-center">Стоимость(без НДС)</td>
          <td vAlign="bottom" class="header-center">Стоимость(c НДС)</td>

          <td vAlign="bottom" class="header-center">Маржа</td>
{/if}
        </tr>

{foreach from=$stats item=item key=key name=outer}
    <tr class="{if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}"{if $smarty.foreach.outer.last} style="border-top: 1px solid #b0b0b0;"{else}{/if}>
{if $type=='call'}
    <td style="color:gray">{if isset($item.id)}{$item.id}{/if}</td>
    <td>
        {if $key !== 'total'}
            {$item.tsf1|datetime_with_timezone:$timezone}
        {else}
            {$item.tsf1}
        {/if}
    </td>
    {if $phone=='all_regions'}<td>{$item.reg_id}</td>{/if}
    <td style="color: {if isset($item.orig) && $item.orig === false}blue;">&darr;&nbsp;входящий{elseif isset($item.orig) && $item.orig === true}green">&uarr;&nbsp;исходящий{else}">{/if}</td>

    <td style="border-left: 1px solid #cccccc;">{if isset($item.src_number)}{$item.src_number}{/if}</td>
    <td>
        <small>
            {$item.geo}
        </small>
    </td>
    <td>
        <small>
            {$item.operator}
        </small>
    </td>

    <td style="border-left: 1px solid #cccccc;">{if isset($item.dst_number)}{$item.dst_number}{/if}</td>
    <td>
        <small>
            {$item.cr2_geo}
        </small>
    </td>
    <td>
        <small>
            {$item.cr2_operator}
        </small>
    </td>

    {if ($key === 'total') || $item.cr2_id}
        <td class="text-center" style="border-left: 1px solid #cccccc;"><b>{$item.tsf2}</b></td>
    {else}
        <td class="text-center" style="border-left: 1px solid #cccccc;color:grey;">
            <small>
                {$item.tsf2}
            </small>
        </td>
    {/if}
    <td class="text-center"><b>{$item.tsf22}</b></td>

    {if ($key === 'total') || $item.cr2_id}
        <td class="text-center" style="border-left: 1px solid #cccccc;">{$item.cost_price}</td>
    {else}
        <td class="text-center" style="border-left: 1px solid #cccccc;color:grey;">
            <small>
                {$item.cost_price}
            </small>
        </td>
    {/if}

    {if $key === 'total'}
        <td>
            <small>
                <b>{$item.cost_price_with_tax} - Сумма с НДС</b>
            </small>
        </td>
    {else}
        <td>
            <small>
                {if isset($item.package_minute)}
                    <span class="profit_package_taken_{$item.package_minute.taken}">
                        {$item.package_minute.name} / минуты ({$item.package_minute.rate})
                    </span><br />
                {/if}
                {if isset($item.package_price)}
                    <span class="profit_package_taken_{$item.package_price.taken}">
                        {$item.package_price.name}
                        ({$item.left.rate}{if isset($item.left.rate_with_tax)}
                         / {$item.left.rate_with_tax} с НДС{/if})
                    </span>
                {elseif isset($item.package_pricelist)}
                    <span class="profit_package_taken_{$item.package_pricelist.taken}">
                        {$item.package_pricelist.pricelist}
                        ({$item.left.rate}{if isset($item.left.rate_with_tax)}
                         / {$item.left.rate_with_tax} с НДС{/if})
                    </span>
                {elseif isset($item.package_pricelist_nnp)}
                    <span class="profit_package_taken_{$item.package_pricelist_nnp.taken}">
                        {$item.package_pricelist_nnp.pricelist}
                        ({$item.left.rate}{if isset($item.left.rate_with_tax)}
                         / {$item.left.rate_with_tax} с НДС{/if})
                    </span>
                {elseif !isset($item.package_minute) && isset($item.left.rate)}
                    <span>
                        <i>не указан</i>
                        ({$item.left.rate}{if isset($item.left.rate_with_tax)}
                         / {$item.left.rate_with_tax} с НДС{/if})
                    </span>
                {elseif $item.billed_time}
                    <span>
                        ???
                    </span>
                {/if}
            </small>
        </td>
    {/if}

    {if ($key === 'total') || $item.cr2_id}
        <td class="text-center" style="border-left: 1px solid #cccccc;">{$item.price}</td>
    {else}
        <td class="text-center" style="border-left: 1px solid #cccccc;color:grey;">
            <small>
                {$item.price}
            </small>
        </td>
    {/if}

    {if $key === 'total'}
        <td>
            <small>
                <b>{$item.price_with_tax} - Сумма с НДС</b>
            </small>
        </td>
    {else}
        <td>
            <small>
                {if isset($item.cr2_package_minute)}
                    <span class="profit_package_taken_{$item.cr2_package_minute.taken}">
                        {$item.cr2_package_minute.name} / Минут: {$item.cr2_package_minute.minute} ({$item.cr2_package_minute.rate})
                    </span> <br/>
                {/if}
                {if isset($item.cr2_package_price)}
                    <span class="profit_package_taken_{$item.cr2_package_price.taken}">
                        {$item.cr2_package_price.name}
                        ({$item.right.rate}{if isset($item.right.rate_with_tax)}
                         / {$item.right.rate_with_tax} с НДС{/if})
                    </span><br />
                {elseif isset($item.cr2_package_pricelist)}
                    <span class="profit_package_taken_{$item.cr2_package_pricelist.taken}">
                        {$item.cr2_package_pricelist.pricelist}
                        ({$item.right.rate}{if isset($item.right.rate_with_tax)}
                         / {$item.right.rate_with_tax} с НДС{/if})
                    </span>
                {elseif isset($item.cr2_package_pricelist_nnp)}
                    <span class="profit_package_taken_{$item.cr2_package_pricelist_nnp.taken}">
                        {$item.cr2_package_pricelist_nnp.pricelist}
                        ({$item.right.rate}{if isset($item.right.rate_with_tax)}
                         / {$item.right.rate_with_tax} с НДС{/if})
                    </span>
                {elseif !isset($item.cr2_package_minute) && isset($item.right.rate)}
                    <span>
                        <i>не указан</i>
                        ({$item.right.rate}{if isset($item.right.rate_with_tax)}
                         / {$item.right.rate_with_tax} с НДС{/if})
                    </span>
                {elseif $item.billed_time}
                    <span>
                        ???
                    </span>
                {/if}
            </small>
        </td>
    {/if}

    {if $item.cr2_id}
        <td class="text-center" style="border-left: 1px solid #b0b0b0;">
            {$item.profit}
        </td>
    {else}
        <td class="text-center" style="border-left: 1px solid #cccccc;color:grey;">
            <small>
                {$item.profit}
            </small>
        </td>
    {/if}
{else}
    <td>{$item.ts1}</td>
    <td class="text-center">{$item.cnt}</td>
    <td class="text-center"><b>{$item.tsf2}</b></td>
    <td class="text-center"><b>{$item.tsf22}</b></td>

    <td class="text-center" style="border-left: 1px solid #b0b0b0;">{$item.cost_price}</td>
    <td class="text-center">{$item.cost_price_with_tax}</td>
    <td class="text-center" style="border-left: 1px solid #b0b0b0;">{$item.price}</td>
    <td class="text-center">{$item.price_with_tax}</td>
    <td class="text-center" style="border-left: 1px solid #b0b0b0;">{$item.profit}</td>
{/if}
    </tr>
{/foreach}
</table>
