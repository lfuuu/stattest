<table border=0 width=100%>
    <tr>
        <td width="33%">
            <a href=/accounting/><img src="images/cash.png"
                                                                                                   title="Счета"
                                                                                                   border=0></a>&nbsp;
            <a href='{$LINK_START}module=newaccounts&action=bill_list&clients_client={$bill_client.id}'
               style="font-weight: bold; font-size: large">
                {$bill_client.client}
            </a>
            <b style="font-weight: bold; font-size: large">{$bill.bill_no}</b>, УЛС: <a href="/client/view?id={$bill.client_id}">{$bill.client_id}</a>

        </td>
        <td>&nbsp;</td>
    </tr>
</table>
<br/>

{*{assign var="discount" value=0}*}
{assign var="sum_vat" value=0}
{assign var="price_without_vat" value=0}
{foreach from=$bill_lines item=item key=key name=outer}
{*    {assign var="discount" value=`$discount+$item.discount_auto+$item.discount_set`}*}
    {assign var="sum_vat" value=`$sum_vat+$item.vat`}
    {assign var="price_without_vat" value=`$price_without_vat+$item.price_without_vat`}
{/foreach}
<table class="table table-condensed table-hover table-striped">
    <tr class=even style='font-weight:bold'>
        {* {if $bill.operation_type_id == 1 || $is_automatic} operation_type_id = 1 - доходный документ *}
            <th>&#8470;</th>
            <th>Наименование</th>
            <th>Период</th>
            <th>Количество</th>
            <th style="text-align: right">Цена ({if $bill.price_include_vat > 0}вкл. НДС{else}без НДС{/if})</th>
            {if $discount != 0}
                <th style="text-align: right">Скидка</th>
            {/if}
            {if $bill.price_include_vat == 0}
                <th style="text-align: right">Сумма</th>
                <th style="text-align: right">Сумма НДС</th>
                <th style="text-align: right">Сумма с НДС</th>
            {else}
                <th style="text-align: right">Сумма</th>
                <th style="text-align: right">Сумма НДС</th>
            {/if}
    </tr>

    {foreach from=$bill_lines item=item key=key name=outer}
        <tr class='{cycle values="odd,even"}'>
            <td>{counter}</td>
            <td>
                <a target="_blank" href="/uu/account-tariff/edit?id={$item.account_tariff_id}">
                    {$item.fullName}
                </a>
            </td>
            <td nowrap>
                    <span>{$item.date_from}
                    <br>
                    {$item.date_to}
                    </span>
            </td>
            <td>{$item.amount|round:6}</td>
            <td style="text-align: right">{$item.price}</td>
            {if $discount != 0}
                {assign var="row_discount" value=`$item.discount_auto+$item.discount_set`}
                <td style="text-align: right">{$row_discount}</td>
            {/if}
            {if $bill.price_include_vat == 0}
                <td style="text-align: right">{$item.price_without_vat}</td>
                <td style="text-align: right">{$item.vat} ({$item.vat_rate}%)</td>
                <td style="text-align: right">{$item.price_with_vat}</td>
            {else}
                <td style="text-align: right">{$item.price_with_vat}</td>
                <td style="text-align: right">{$item.vat} ({$item.vat_rate}%)</td>
            {/if}
        </tr>
    {/foreach}
    {* {/if} *}
    <tr> 
            {* {if $bill.operation_type_id == 1 || $is_automatic}  operation_type_id = 1 - доходный документ *}
            <th colspan=5 style="text-align: left">Итого:</th>
                {if $bill.price_include_vat == 0}
                    <th style="text-align: right">{$price_without_vat|round:2}</th>
                    <td style="text-align: right">{if $bill.sum != 0}{$bill.sum-$price_without_vat|round:2}{else}---{/if}</td>
                    <th style="text-align: right">{$bill.sum|round:2}</th>
                {else}
                    <th style="text-align: right">{if $bill.sum_correction}<strike>{$bill.sum|round:2}</strike>
                            <br>
                            {$bill.sum_correction|round:2}{else}{$bill.sum|round:2}{/if}</th>
                    <td style="text-align: right">в т.ч. {$sum_tax|round:2} </td>
                {/if}
        <td colspan="2" style="text-align: right">&nbsp;</td>
    </tr>
</table>

<hr/>


<style type="text/css">
    {literal}
    .content-wrap {
        width: 300px;
        height: 40px;
        border: 1px dashed grey;
        padding: 4px;
        position: relative;
        clear: both;
    }

    .content-wrap:hover .more-info {
        display: block;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.5);
    }

    .full-text {
        height: 35px;
        overflow: hidden;
    }

    .more-info {
        border: 1px dashed grey;
        background: #ccc;
        position: absolute;
        left: -1px;
        top: -1px;
        right: -1px;
        padding: 4px;
        display: none;
    }

    .uu-bill-view-deleted {
        text-decoration: line-through;
    }
    .uu-bill-view-updated {
        color: grey;
    }

    .uu-bill-view-updated a {
        color: gray !important;
    }

    {/literal}
</style>