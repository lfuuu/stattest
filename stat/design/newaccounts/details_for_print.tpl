{if $bill.is_rollback != 1}
    {capture name=seller}
        {$firm.name}
    {/capture}
    {capture name=customer}
        {if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}
    {/capture}
    {capture name=seller_address}
        {$firm.address}
    {/capture}
    {capture name=customer_address}
        {if $bill_client.head_company_address_jur}{$bill_client.head_company_address_jur}{else}{$bill_client.address_jur}{/if}
    {/capture}
    {capture name=seller_inn}
        {$firm.inn}&nbsp;/&nbsp;{$firm.kpp}
    {/capture}
    {capture name=customer_inn}
        {$bill_client.inn}&nbsp;/&nbsp;{$bill_client.kpp}
    {/capture}
    {capture name=consignor}
        {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}&nbsp;--{else}{$firm.name} {$firm.address}{/if}
    {/capture}
    {capture name=consignee}
        {if isset($client.is_with_consignee) && $client.is_with_consignee && $bill_client.consignee}
            {$bill_client.consignee}
        {else}
            {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}
                &nbsp;--
            {else}
                {$bill_client.company_full}{$bill_client.address_post}
            {/if}
        {/if}
    {/capture}
    {capture name=seller_head_position}
        {if $to_print}
            &nbsp;
        {else}
            Генеральный директор
        {/if}
    {/capture}
    {capture name=seller_head_name}
        {if !$client.is_upd_without_sign && !$to_print}
            {$firm.director}
        {else}
            &nbsp;
        {/if}
    {/capture}
    {capture name=seller_buh_position}
        {if $to_print}
            &nbsp;
        {else}
            {$firm_buh.position}
        {/if}
    {/capture}
    {capture name=seller_buh_name}
        {if !$client.is_upd_without_sign && !$to_print}
            {$firm_buh.name}
        {else}
            &nbsp;
        {/if}
    {/capture}
    {capture name=customer_head_position}
        &nbsp;
    {/capture}
    {capture name=customer_head_name}
        &nbsp;
    {/capture}
    {capture name=customer_buh_position}
        &nbsp;
    {/capture}
    {capture name=customer_buh_name}
        &nbsp;
    {/capture}
    {capture name=seller_firm_info}
        {$firm.name}, ИНН/КПП {$firm.inn}&nbsp;/&nbsp;{$firm.kpp}
    {/capture}
    {capture name=customer_firm_info}
        {if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}, ИНН/КПП {$bill_client.inn}&nbsp;/&nbsp;{$bill_client.kpp}
    {/capture}
{else}
    {capture name=seller}
        {if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}
    {/capture}
    {capture name=customer}
        {$firm.name}
    {/capture}
    {capture name=seller_address}
        {if $bill_client.head_company_address_jur}{$bill_client.head_company_address_jur}{else}{$bill_client.address_jur}{/if}
    {/capture}
    {capture name=customer_address}
        {$firm.address}
    {/capture}
    {capture name=seller_inn}
        {$bill_client.inn}&nbsp;/&nbsp;{$bill_client.kpp}
    {/capture}
    {capture name=customer_inn}
        {$firm.inn}&nbsp;/&nbsp;{$firm.kpp}
    {/capture}
    {capture name=consignor}
        {if isset($bill_client.is_with_consignee) && $bill_client.is_with_consignee && $bill_client.consignee}{$bill_client.consignee}{else}{if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}&nbsp;--{else}{$bill_client.company_full}{$bill_client.address_post}{/if}{/if}
    {/capture}
    {capture name=consignee}
        {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}&nbsp;--{else}{$firm.name} {$firm.address}{/if}
    {/capture}
    {capture name=seller_head_position}
        &nbsp;
    {/capture}
    {capture name=seller_head_name}
        &nbsp;
    {/capture}
    {capture name=seller_buh_position}
        &nbsp;
    {/capture}
    {capture name=seller_buh_name}
        &nbsp;
    {/capture}
    {capture name=customer_head_position}
        Генеральный директор
    {/capture}
    {capture name=customer_head_name}
        {if !$client.is_upd_without_sign}
            {$firm.director}
        {else}
            &nbsp;
        {/if}
    {/capture}
    {capture name=customer_buh_position}
        {$firm_buh.position}
    {/capture}
    {capture name=customer_buh_name}
        {if !$client.is_upd_without_sign}
            {$firm_buh.name}
        {else}
            &nbsp;
        {/if}
    {/capture}
    {capture name=seller_firm_info}
        {if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}, ИНН/КПП {$bill_client.inn}&nbsp;/&nbsp;{$bill_client.kpp}
    {/capture}
    {capture name=customer_firm_info}
        {$firm.name}, ИНН/КПП {$firm.inn}&nbsp;/&nbsp;{$firm.kpp}
    {/capture}
{/if}
