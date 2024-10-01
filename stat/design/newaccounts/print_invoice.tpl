<html lang="ru">
<head>
    <LINK title=default href="{if $is_pdf == '1'}{$WEB_PATH}{else}{$PATH_TO_ROOT}{/if}invoice.css" type=text/css
          rel=stylesheet>
    <title>СЧЕТ-ФАКТУРА N {if !$inv_number}{$bill.bill_no}{$inv_no}{else}{$inv_number}{/if}
        от {$inv_date|mdate:"d.m.Y г."}</title>
    <META http-equiv=Content-Type content="text/html; charset=utf-8">
    <style>
        @page {literal} {
            size: landscape;
        }

        @page rotated {
            size: landscape;
        }

        {/literal}
        {if $inv_is_new5}
        {literal}
        .ht {
            font-size: 9pt;
        }

        .ht strong {
            font-size: 9pt;
        }

        .head_table td strong {
            font-size: 7pt;
        }

        .hst {
            width: 100%
        }

        .hst td {
            font-size: 9pt;
        }

        .head_table .hst td {
            font-size: 7pt;
        }

        .hst .ff {
            width: 5%;
            padding-right: 1px;
            white-space: nowrap;
            padding-left: 130px;
        }

        .fs {
            font-size: {/literal}{if $inv_is_new7}9{else}11{/if}{literal}pt !important;
        }

        .hst .f {
            width: 5%;
            padding-right: 1px;
            white-space: nowrap;
        }

        .hst .n {
            border-bottom: 1px solid black;
            width: 94%;
        }

        .hst .l {
            width: 10px;
        }

        #main_table th {
            font-size: 5pt;
        }

        {/literal}
        {/if}
    </style>
</head>

<body bgcolor="#FFFFFF" text="#000000">
{if $inv_date >= strtotime("2017-08-01")}
    {assign var="isChanges20170801" value=1}
{else}
    {assign var="isChanges20170801" value=0}
{/if}

{if $inv_date >= strtotime("2017-10-01")}
    {assign var="isChanges20171001" value=1}
{else}
    {assign var="isChanges20171001" value=0}
{/if}

{if $negative_balance}<h2 style="color:red">Внимание! Не достаточно средств для проведения авансовых платежей!</h2>{/if}
<table border="0" cellpadding="0" cellspacing="1{if !$inv_is_new7}5{/if}">
    {if $inv_is_new3}
        <tr>
        <td colspan="2"><p style="text-align:center;">
        <strong>
        {if !$is_document_ready}<b style="color:red;">***ДОКУМЕНТ ДЛЯ ВНУТРЕННЕГО ИСПОЛЬЗОВАНИЯ***</b><br>{/if}
        {if !$inv_is_new7}
            СЧЕТ-ФАКТУРА N&nbsp;{if !$inv_number}{if $is_four_order eq true}AB-{/if}{$bill.bill_no}{$inv_no}{else}{$inv_number}{/if}
            {if !$without_date_date}
                от {if $is_four_order && isset($inv_pays)}
                {$inv_pays[0].payment_date_ts|mdate:"d.m.Y г."}
            {else}
                {$inv_date|mdate:"d.m.Y г."}
            {/if}
            {else}
                {$without_date_date|mdate:"от d.m.Y г."}
            {/if}
            <br>
            ИСПРАВЛЕНИЕ N {if $invoice && $invoice.correction_idx}{$invoice.correction_idx} от {$invoice.date|mdate:"d.m.Y г."}{else}{if $correction_info}{$correction_info.number} от {$correction_info.date_timestamp|mdate:"d.m.Y г."}{else}----- от -----{/if}{/if}</strong></p></tr>
        {/if}
    {/if}
    <tr>
        <td valign="top" width="55%" class="ht{if $inv_is_new7} head_table{/if}">
            {if !$inv_is_new7}
                {if $bill_client.firma=='all4geo'}
                    Продавец:
                    <strong>Общество с ограниченной ответственностью "Олфогео"</strong>
                    <br>
                    Адрес:
                    <strong>115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8</strong>
                    <br>
                    ИНН/КПП продавца:
                    <strong>7727752091 / 772401001</strong>
                    <br>
                    Грузоотправитель и его адрес:
                    <strong>ООО "Олфогео"</strong>
                    <br>
                    <strong>115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8</strong>
                    <br>
                {elseif $bill_client.firma=='ooomcn'}
                    Продавец:
                    <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)}Общество с ограниченной ответственностью "МСН" {if $bill.bill_date < '2012-01-24'}(ООО "МСН"){/if}{else}ООО "МСН"{/if}</strong>
                    <br>
                    Адрес:
                    <strong>117574 г. Москва, Одоевского пр-д., д. 3, кор. 7</strong>
                    <br>
                    ИНН/КПП продавца:
                    <strong>7728638151 / 772801001</strong>
                    <br>
                    Грузоотправитель и его
                    адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}
                    <strong>ООО "МСН"</strong>
                    <br>
                    <strong>117574 г. Москва, Одоевского пр-д., д. 3, кор. 7</strong>
                    <br>
                {/if}
                {elseif $bill_client.firma=='ooocmc'}
                    Продавец:
                    <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)}Общество с ограниченной ответственностью "Си Эм Си"{if $bill.bill_date < '2012-01-24'} (ООО "Си Эм Си"){/if}{else}ООО "Си Эм Си"{/if}</strong>
                    <br>
                    Адрес:
                    <strong>117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97</strong>
                    <br>
                    ИНН/КПП продавца:
                    <strong>7727701308 / 772701001</strong>
                    <br>
                    Грузоотправитель и его
                    адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}
                    <strong>ООО "Си Эм Си"</strong>
                    <br>
                    <strong>117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97</strong>
                    <br>
                {/if}
                {elseif $bill_client.firma=='all4net'}
                    Продавец: {if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)}Общество с ограниченной ответственностью "Олфонет"{if $bill.bill_date < '2012-01-24'} (ООО "Олфонет"){/if}{else}ООО "Олфонет"{/if}</strong>
                    <br>
                    {if $bill.ts >= strtotime("2013-08-13")}
                        Адрес:
                        <strong>117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130</strong>
                        <br>
                    {else}
                        Адрес:
                        <strong>117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97</strong>
                        <br>
                    {/if}
                    ИНН/КПП продавца:
                    <strong>7727731060 / 772701001</strong>
                    <br>
                    Грузоотправитель и его
                    адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}
                    <strong>ООО "Олфонет"</strong>
                    <br>
                    {if $bill.ts >= strtotime("2013-08-13")}
                        Адрес:
                        <strong>117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130</strong>
                        <br>
                    {else}
                        Адрес:
                        <strong>117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97</strong>
                        <br>
                    {/if}
                {/if}

                {elseif $bill_client.firma == "markomnet"}
                    Продавец:
                    <strong>ООО "МАРКОМНЕТ"</strong>
                    <br>
                    Адрес:
                    <strong>123458, г. Москва, Таллинская ул., д.2, кв. 282</strong>
                    <br>
                    {if !$isChanges20170801}Телефон:<strong>(095) 950-5678</strong><br>{/if}
                    ИНН/КПП продавца:
                    <strong>7734246040&nbsp;/&nbsp;773401001</strong>
                    <br>
                    Грузоотправитель и его
                    адрес: {if ('2009-06-01' <= $bill.bill_date && $invoice_source != 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}
                    <strong>ООО "МАРКОМНЕТ"</strong>
                    <br>
                    <strong>123458, г. Москва, Таллинская ул., д.2/282</strong>
                    <br>
                {/if}
                {elseif $bill_client.firma == "mcn_telekom"}
                    Продавец:
                    <strong>{$firm.name}</strong>
                    <br>
                    Адрес:
                    <strong>{$firm.address}</strong>
                    <br>
                    {if !$isChanges20170801}Телефон:<strong>{$firm.phone}</strong><br>{/if}
                    ИНН/КПП продавца:
                    <strong>{$firm.inn}&nbsp;/&nbsp;{$firm.kpp}</strong>
                    <br>
                    Грузоотправитель и его
                    адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="41" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}
                    <strong>ООО "МСН Телеком"</strong>
                    <br>
                    <strong>{$firm.address}</strong>
                    <br>
                {/if}

                    {if false}
                        Продавец:
                        <strong>ООО "МСН Телеком"</strong>
                        <br>
                        Адрес:
                        <strong>115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8</strong>
                        <br>
                        {if !$isChanges20170801}Телефон:<strong>(495) 950-56-78</strong><br>{/if}
                        ИНН/КПП продавца:
                        <strong>7727752084&nbsp;/&nbsp;772401001</strong>
                        <br>
                        Грузоотправитель и его
                        адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="41" name="mysec"}&nbsp;{/section}------
                        <br/>
                    {else}
                        <strong>ООО "МСН Телеком"</strong>
                        <br>
                        <strong>115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8</strong>
                        <br>
                    {/if}
                    {/if}

                {elseif $bill_client.firma == "markomnet_service"}
                    Продавец:
                    <strong>ООО "Маркомнет сервис"</strong>
                    <br>
                    Адрес:
                    <strong>117574, Москва, Одоевского проезд, д.3, к.7</strong>
                    <br>
                    {if !$isChanges20170801}Телефон:<strong>(495) 638-63-84</strong><br>{/if}
                    ИНН/КПП продавца:
                    <strong>7728802130&nbsp;/&nbsp;772801001</strong>
                    <br>
                    Грузоотправитель и его
                    адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="41" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}
                    <strong>ООО "Маркомнет сервис"</strong>
                    <br>
                    <strong>117574, Москва, Одоевского проезд, д.3, к.7</strong>
                    <br>
                {/if}
                {elseif $bill_client.firma == "mcm"}
                    Продавец:
                    <strong>ООО "МСМ"</strong>
                    <br>
                    Адрес:
                    <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong>
                    <br>
                    {if !$isChanges20170801}Телефон:<strong>(495) 950-58-41</strong><br>{/if}
                    ИНН/КПП продавца:
                    <strong>7727667833&nbsp;/&nbsp;772701001</strong>
                    <br>
                    Грузоотправитель и его адрес:
                    <strong>ООО "МСМ"</strong>
                    <br>
                    <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong>
                    <br>
                {elseif $bill_client.firma == "markomnet_new"}
                    Продавец:
                    <strong>ООО "МАРКОМНЕТ"</strong>
                    <br>
                    Адрес:
                    <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong>
                    <br>
                    {if !$isChanges20170801}Телефон:<strong>638-638-4</strong><br>{/if}
                    ИНН/КПП продавца:
                    <strong>7727702076&nbsp;/&nbsp;772701001</strong>
                    <br>
                    Грузоотправитель и его адрес:
                    <strong>ООО "МАРКОМНЕТ"</strong>
                    <br>
                    <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong>
                    <br>
                {elseif $bill_client.firma=='mcn'}{* || ($bill_client.nal=='beznal' && $bill.ts>=strtotime('2006-07-01') && $bill.comment!="разбивка Markomnet")*}
                    Продавец:
                    <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)}Общество с ограниченной ответственностью "Эм Си Эн"{if $bill.bill_date < '2012-01-24'} (ООО "Эм Си Эн"){/if}{else}ООО "Эм Си Эн"{/if}</strong>
                    <br>
                    Адрес:
                    <strong>113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130</strong>
                    <br>
                    ИНН/КПП продавца:
                    <strong>7727508671 / 772701001</strong>
                    <br>
                    Грузоотправитель и его
                    адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}
                    <strong>ООО "Эм Си Эн"</strong>
                    <br>
                    <strong>113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130</strong>
                    <br>
                {/if}
                {else}
                    {if !$inv_is_new7}
                        Продавец:
                        <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)}{$firm.name_full}{if $bill.bill_date < '2012-01-24'} ({$firm.name}){/if}{else}{$firm.name}{/if}</strong>
                        <br>
                        Адрес:
                        <strong>{$firm.address}</strong>
                        <br>
                        ИНН/КПП продавца:
                        <strong>{$firm.inn} / {$firm.kpp}</strong>
                        <br>
                        Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------
                        <br/>
                    {else}<strong>{$firm.name}</strong>
                        <br>
                        <strong>{$firm.address}</strong>
                        <br>
                    {/if}
                    {/if}
                {/if}
            {/if}

            {if !$inv_is_new7}
                Грузополучатель и его адрес: {if isset($bill_client.is_with_consignee) && $bill_client.is_with_consignee && $bill_client.consignee}
                <strong>{$bill_client.consignee}</strong>
                <br>
            {else}{if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="42" name="mysec"}&nbsp;{/section}------
                    <br/>
                {else}<strong>{$bill_client.company_full}</strong>
                    <br>
                    <strong>{$bill_client.address_post}</strong>
                    <br>
                {/if}{/if}
                К платежно-расчетному документу{if isset($inv_pays)} {foreach from=$inv_pays item=inv_pay name=outer}N{$inv_pay.payment_no} от {$inv_pay.payment_date_ts|mdate:"d.m.Y г."}{if !$smarty.foreach.outer.last}, {/if}{/foreach}{/if}
                <br>
                {if $inv_is_new7}Документ об отгрузке № п/п {section loop="43" name="mysec"}&nbsp;{/section}------
                    <br>
                {/if}
                Покупатель:
                <strong>{if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}</strong>
                <br>
                Адрес:
                <strong>{$bill_client.address}</strong>
                <br>
                ИНН/КПП покупателя:
                <strong>{$bill_client.inn}&nbsp;/{$bill_client.kpp}</strong>
                <br>
                {if !$isChanges20170801}Дополнение:<strong>к счету N: {$bill.bill_no}</strong><br>{/if}
                {if $inv_is_new3}Валюта: наименование Российский рубль, код 643{/if}
                {*'2017-07-01' = 1498867200*}{if 1498867200 <= $inv_date}
                <br>
                Идентификатор государственного контракта, договора (соглашения){if $isChanges20171001} (при наличии){/if}:
                <br/>
                <br/>
            {/if}
            {/if}

            {if $inv_is_new7}
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="ff fs">СЧЕТ-ФАКТУРА №&nbsp;</td>
                        <td class="n fs">{capture name=invoice_name}{if !$inv_number}{if $is_four_order eq true}AB-{/if}{$bill.bill_no}{$inv_no}{else}{$inv_number}{/if}
                            {if !$without_date_date}
                                от {if $is_four_order && isset($inv_pays)}
                                {$inv_pays[0].payment_date_ts|mdate:"d.m.Y г."}
                            {else}
                                {$inv_date|mdate:"d.m.Y г."}
                            {/if}
                            {else}
                                {$without_date_date|mdate:"от d.m.Y г."}
                            {/if}{/capture}{$smarty.capture.invoice_name}</td>
                        <td class="l">(1)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="ff fs">ИСПРАВЛЕНИЕ №</td>
                        <td class="n fs">
                            {if $invoice && $invoice.correction_idx}
                                {$invoice.correction_idx} от {$invoice.date|mdate:"d.m.Y г."}
                            {else}
                                {if $correction_info}
                                    {$correction_info.number} от {$correction_info.date_timestamp|mdate:"d.m.Y г."}
                                {else}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; от &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;----- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                {/if}
                            {/if}
                        </td>
                        <td class="l">(1а)</td>
                    </tr>
                </table>
                <table border="0" class="hst">
                    <tr>
                        <td class="f">Продавец:</td>
                        <td class="n">
                            <strong>
                                {if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)}{$firm.name_full}{if $bill.bill_date < '2012-01-24'} ({$firm.name}){/if}{else}{$firm.name}{/if}
                            </strong>
                        </td>
                        <td class="l">(2)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">Адрес:</td>
                        <td class="n"><strong>{$firm.address}</strong></td>
                        <td class="l">(2а)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">ИНН/КПП продавца:</td>
                        <td class="n"><strong>{$firm.inn} / {$firm.kpp}</strong></td>
                        <td class="l">(2б)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">Грузоотправитель и его адрес:</td>
                        <td class="n">{if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------{else}
                                <strong>{$firm.name}</strong>
                                <strong>{$firm.address}</strong>{/if}</td>
                        <td class="l">(3)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">Грузополучатель и его адрес:</td>
                        <td class="n">{if isset($bill_client.is_with_consignee) && $bill_client.is_with_consignee && $bill_client.consignee}
                                <strong>{$bill_client.consignee}</strong>
                                <br>
                            {else}{if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source != 2)) && $invoice_source != 3) || $is_four_order}{section loop="42" name="mysec"}&nbsp;{/section}------{else}
                                    <strong>{$bill_client.company_full}</strong>
                                    <strong>{$bill_client.address_post}</strong>{/if}{/if}</td>
                        <td class="l">(4)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">К платежно-расчетному документу</td>
                        <td class="n">{if isset($inv_pays)} {foreach from=$inv_pays item=inv_pay name=outer}N{$inv_pay.payment_no} от {$inv_pay.payment_date_ts|mdate:"d.m.Y г."}{if !$smarty.foreach.outer.last}, {/if}{/foreach}{/if}</td>
                        <td class="l">(5)</td>
                    </tr>
                {if $inv_is_new8}
                    <table border="0" cellpadding="0" cellspacing="0" class="hst">
                        <tr>
                            <td class="f">Документ об отгрузке: наименование, №</td>
                            <td class="n">{if ($invoice_source != 3 && $invoice_source != 4) || $shipped_date}{if $bill_lines|@count > 1}1-{$bill_lines|@count}{else}1{/if} № {$smarty.capture.invoice_name}{else}{section loop="43" name="mysec"}&nbsp;{/section}------{/if}</td>
                            <td class="l">(5а)</td>
                        </tr>
                    </table>                </table>
                {elseif $inv_is_new7}
                    <table border="0" cellpadding="0" cellspacing="0" class="hst">
                        <tr>
                            <td class="f">Документ об отгрузке № п/п</td>
                            <td class="n">{if ($invoice_source != 3 && $invoice_source != 4) || $shipped_date}{if $bill_lines|@count > 1}1-{$bill_lines|@count}{else}1{/if} № {$smarty.capture.invoice_name}{else}{section loop="43" name="mysec"}&nbsp;{/section}------{/if}</td>
                            <td class="l">(5а)</td>
                        </tr>
                    </table>
                {/if}
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">Покупатель:</td>
                        <td class="n">
                            <strong>{if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}</strong>
                        </td>
                        <td class="l">(6)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">Адрес:</td>
                        <td class="n">
                            <strong>{$bill_client.address}</strong>
                        </td>
                        <td class="l">(6а)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">ИНН/КПП покупателя:</td>
                        <td class="n"><strong>{$bill_client.inn}&nbsp;/{$bill_client.kpp}</strong></td>
                        <td class="l">(6б)</td>
                    </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">Валюта: наименование, код &nbsp;&nbsp;</td>
                        <td class="n">Российский рубль, 643</td>
                        <td class="l">(7)</td>
                    </tr>
                </table>
                {*'2017-07-01' = 1498867200*}{if 1498867200 <= $inv_date}
                <table border="0" cellpadding="0" cellspacing="0" class="hst">
                    <tr>
                        <td class="f">Идентификатор государственного контракта, договора
                            (соглашения){if $isChanges20171001} (при наличии){/if}:
                        </td>
                        <td class="n">{section loop="43" name="mysec"}&nbsp;{/section}----</td>
                        <td class="l">(8)</td>
                    </tr>
                </table>
            {/if}
                </div>

            {/if}

        </td>

        <td style="text-align:right;" valign="top" width="45%">
            {if !$inv_is_new7}<small>{/if} Приложение N1<br>
                {if $inv_is_new3}к постановлению Правительства
                    <br>
                    Российской Федерации
                    <br>
                    от 26 декабря 2011 г. N 1137
                    <br/>
                    {if $inv_is_new8}
                        (в ред. Постановления Правительства РФ от 16.08.2024 № 1096)
                    {elseif $inv_is_new7}
                        (в ред. Постановления Правительства РФ от 02.04.2021 № 534)
                    {elseif $isChanges20171001}
                        (в ред. Постановления Правительства РФ от 19.08.2017 № 981)
                    {elseif 1498867200 <= $inv_date}
                        <small class="sm">(в ред. Постановления Правительства РФ от 25.05.2017 N625):</small>
                    {/if}

                {else}к Правилам ведения журналов учета полученных и выставленных счетов-фактур,
                    <br>
                    книг покупок и книг продаж при расчетах но налогу на добавленную стоимость,
                    <br>
                    утвержденным постановлением правительства Российской Федерации от 2 декабря 2000 г. N 914
                    <br>
                    (в редакции постановлений Правительства Российской Федерации
                    <br>
                    от 10 марта 2001 г. N 189, от 27 июля 2002 г. N 575, от 16 февраля 2004 г. N 84{if $inv_is_new}, от 11 мая 2006 г. N 283{/if}{if $inv_is_new2}, от 26 мая 2009 г. N 451{/if}{if $inv_is_new3}, от 26 декабря 2011 г. N 1137{/if}){/if}
                {if !$inv_is_new7}</small>{/if}</td>

    </tr>
    <tr>

        <td colspan="2">
            {if $inv_is_new7}<br />{/if}
            {if !$inv_is_new3}<p style="text-align:center;"><strong>СЧЕТ-ФАКТУРА
                    N&nbsp;{if !$inv_number}{if $is_four_order eq true}AB-{/if}{$bill.bill_no}{$inv_no}{else}{$inv_number}{/if}
                    {if !$without_date_date}
                        от  {if $is_four_order && isset($inv_pays)}
                        {$inv_pays[0].payment_date_ts|mdate:"d.m.Y г."}
                    {else}
                        {$inv_date|mdate:"d.m.Y г."}
                    {/if}
                    {else}
                        {$without_date_date|mdate:"от d.m.Y г."}
                    {/if}</strong></p>
            {/if}
            {if !$bill.sum_tax}Для официальных нужд международной организации.<br>{/if}
            {if !$inv_is_new3}Валюта: руб.{/if}
            {*if !$inv_is_new3}Наименование и код валюты: руб. (643){/if*}
            <div style="text-align:center;">
                <center>
                    <table border="1" cellpadding="3" cellspacing="0" width="100%"{if $inv_is_new7} id="main_table"{/if}>
                        <tr>
                            {if $inv_is_new7}
                                <th rowspan="2" nowrap="nowrap">№<br>п/п</th>
                            {/if}
                            <th{if $inv_is_new3} rowspan=2{/if}{if $inv_is_new7} style="min-width: 220px;"{/if}>Наименование<br>товара<br>(описание выполненных
                                работ, оказанных услуг){if $inv_is_new},<br>имущественного права{/if}</th>
                            {if $isChanges20171001}
                                <th rowspan=2>Код<br/> вида<br/> товара</th>
                            {/if}
                            {if !$inv_is_new3}
                                <th>Еди-<br>ница<br>изме-<br>рения</th>
                            {else}
                                <th colspan=2>Единица<br>измерения</th>
                            {/if}
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                Коли-<br>чество{if $inv_is_new6}<br>(объем){/if}
                            </th>
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                {if $inv_is_new6}
                                    Цена
                                    <br>
                                    (тариф)
                                    <br>
                                    за еди-
                                    <br>
                                    ницу изме-
                                    <br>
                                    рения
                                {else}
                                    Цена
                                    <br>
                                    (та-
                                    <br>
                                    риф)
                                    <br>
                                    за еди-
                                    <br>
                                    ницу изме-
                                    <br>
                                    рения{if $inv_is_new3}<br>руб{/if}
                                {/if}
                            </th>
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                {if $inv_is_new6}
                                    Стоимость
                                    <br>
                                    товаров (работ,
                                    <br>
                                    услуг){if $inv_is_new},<br>имущественных<br>прав{/if} без налога-
                                    <br>
                                    всего
                                {else}
                                    Стои-
                                    <br>
                                    мость
                                    <br>
                                    товаров
                                    <br>
                                    (работ,
                                    <br>
                                    услуг){if $inv_is_new},<br>имущественных<br>прав{/if}, всего без
                                    <br>
                                    налога{if $inv_is_new3}<br>руб{/if}
                                {/if}
                            </th>
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                в том<br>чис-<br>ле<br>{if $inv_is_new6}сумма<br>{/if}акциз{if $inv_is_new6}а{/if}
                            </th>
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                Нало-<br>говая<br>ставка
                            </th>
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                Сумма{if $inv_is_new6} налога,<br> предъявляемая<br>покупателю{else}<br>нало-
                            <br>га,{if $inv_is_new3}<br>руб{/if}{/if}
                            </th>
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                {if $inv_is_new6}
                                    Стоимость товаров
                                    <br>
                                    (работ, услуг),
                                    <br>
                                    имущественных
                                    <br>
                                    прав с налогом-
                                    <br>
                                    всего
                                {else}
                                    Стоимость
                                    <br>
                                    товаров
                                    <br>
                                    (работ,
                                    <br>
                                    услуг)
                                    {if $inv_is_new},<br>имущественных прав{/if}
                                    ,
                                    <br>
                                    всего с
                                    <br>
                                    учетом
                                    <br>
                                    налога
                                    {if $inv_is_new3}
                                        <br>
                                        руб
                                    {/if}

                                {/if}</th>
                            {if !$inv_is_new3}
                                <th>Стра-<br>на проис-<br> хожде-<br> ния</th>
                            {else}
                                <th colspan=2>Страна<br>происхождения<br>товара</th>
                            {/if}
                            <th{if $inv_is_new3} rowspan=2{/if}>
                                {if $inv_is_new7}
                                    Регистрационный номер<br/> декларации<br/> на товары или<br/> регистрационный номер<br/> партии товаров,<br/> подлежащих<br/> прослеживаемости
                                {elseif $isChanges20171001}
                                    Регистра-
                                    <br/>
                                    ционный
                                    <br/>
                                    номер
                                    <br/>
                                    тамо-
                                    <br/>
                                    женной
                                    <br/>
                                    декла-
                                    <br/>
                                    рации
                                {else}
                                    Номер
                                    <br>
                                    тамо-
                                    <br>
                                    женной
                                    <br>
                                    декла-
                                    <br>
                                    рации
                                {/if}</th>
                            {if $inv_is_new7}
                                <th colspan="2">Количественная единица<br> измерения товара,<br> используемая в
                                    целях<br> осуществления<br> прослеживаемости
                                </th>
                                <th rowspan="2">Количество товара,<br> подлежащего<br> прослеживаемости, в<br>
                                    количественной единице<br> измерения товара,<br> используемой в целях<br>
                                    осуществления<br> прослеживаемости
                                </th>
                            {/if}
                            {if $inv_is_new3}
                        <tr>
                            <th>к<br>о<br>д</th>
                            <th>условное<br>обозначение<br>(национальное)</th>
                            <th>цифровой<br>код</th>
                            <th>краткое<br>наимено-<br>вание</th>
                            {if $inv_is_new7}
                                <th>код</th>
                                <th>условное<br> обозначение</th>
                            {/if}
                        </tr>{/if}

                        <tr>
                            {if $inv_is_new7}
                                <td style="text-align:center;">1</td>
                            {/if}
                            <td style="text-align:center;">{if $inv_is_new7}1а{else}1{/if}</td>
                            {if $isChanges20171001}
                                <td style="text-align:center;">1{if $inv_is_new7}б{else}а{/if}</td>
                            {/if}
                            <td style="text-align:center;">2</td>
                            {if $inv_is_new3}
                                <td style="text-align:center;">2а</td>
                            {/if}
                            <td style="text-align:center;">3</td>
                            <td style="text-align:center;">4</td>
                            <td style="text-align:center;">5</td>
                            <td style="text-align:center;">6</td>
                            <td style="text-align:center;">7</td>
                            <td style="text-align:center;">8</td>
                            <td style="text-align:center;">9</td>
                            <td style="text-align:center;">10</td>
                            {if $inv_is_new3}
                                <td style="text-align:center;">10а</td>
                            {/if}
                            <td style="text-align:center;">11</td>
                            {if $inv_is_new7}
                                <td style="text-align:center;">12</td>
                                <td style="text-align:center;">12а</td>
                                <td style="text-align:center;">13</td>
                            {/if}
                        </tr>
                        {foreach from=$bill_lines item=row key=key name=lines}
                            <tr>
                                {if $inv_is_new7}
                                    <td style="text-align:center;">{$smarty.foreach.lines.iteration}</td>{/if}
                                <td>{if $is_four_order}Предварительная оплата<br>{else}{$row.item}{/if}</td>
                                {if $isChanges20171001}
                                    <td style="text-align:center;">-</td>
                                {/if}
                                {if $inv_is_new3}
                                    <td style="text-align:center;">

                                        {*if $inv_is_new4 && $line.type == "service"}-{else}ЫФ.{/if*}

                                        {*if $inv_is_new4}
                                            {if $row.type == "service"}
                                                -
                                            {else}
                                                {if $row.okvd_code}{$row.okvd}{else}шт.{/if}
                                            {/if}
                                        {else}
                                           шт.
                                        {/if*}

                                        {if $is_four_order}
                                            -
                                        {else}
                                            {if $inv_is_new4}
                                                {if isset($row.okvd_code) && $row.okvd_code}
                                                    {$row.okvd_code|string_format:"%03d"}
                                                {else}
                                                    {if $row.type == "service"}
                                                        -
                                                    {else}
                                                        796
                                                    {/if}
                                                {/if}
                                            {else}
                                                796
                                            {/if}
                                        {/if}
                                        {*if $row.okvd_code && !$is_four_order}{$row.okvd_code|string_format:"%03d"}{else}
                                            {if $is_four_order || ($row.type == "service")}-{else}796{/if}
                                        {/if*}</td>
                                {/if}
                                <td style="text-align:center;">
                                    {if $is_four_order}
                                        -
                                    {else}
                                        {if $inv_is_new4}
                                            {if isset($row.okvd_code) && $row.okvd_code}
                                                {$row.okvd_code|okei_name}
                                            {else}
                                                {if $row.type == "service"}
                                                    -
                                                {else}
                                                    шт.
                                                {/if}
                                            {/if}
                                        {else}
                                            шт.
                                        {/if}
                                    {/if}
                                    {*if $row.okvd_code && !$is_four_order}{$row.okvd}{else}{if $is_four_order || ($row.type == "service")}-{else}шт.{/if}{/if*}</td>

                                <td style="text-align:center;">
                                    {if $bill.bill_date < "2012-01-01"}{$row.amount|round:4}{else}
                                        {if $is_four_order}
                                            -
                                        {else}
                                            {if $inv_is_new4}
                                                {if isset($row.okvd_code) && $row.okvd_code}
                                                    {$row.amount|round:4}
                                                {else}

                                                    {if $row.type == "service"}
                                                        -
                                                    {else}
                                                        {$row.amount|round:4}
                                                    {/if}
                                                {/if}
                                            {else}
                                                {$row.amount|round:4}
                                            {/if}
                                        {/if}
                                        {*if $is_four_order}-{else}{if $row.okvd_code || $row.type != "service"}{$row.amount|round:4}{else}-{/if}{/if*}

                                    {/if}
                                </td>
                                <td style="text-align:center;">


                                    {if $is_four_order}
                                        -
                                    {else}
                                        {if $inv_is_new4}
                                            {if isset($row.okvd_code) && $row.okvd_code}
                                                {$row.price|round:2}
                                            {else}
                                                {if $row.type == "service"}
                                                    -
                                                {else}
                                                    {$row.price|round:2}
                                                {/if}
                                            {/if}
                                        {else}
                                            {$row.price|round:2}
                                        {/if}
                                    {/if}


                                </td>
                                <td style="text-align:center;">
                                    {if $is_four_order}
                                        -
                                    {else}

                                        {$row.sum_without_tax|round:2}
                                    {/if}
                                </td>
                                <td style="text-align:center;" nowrap>{if $inv_is_new4}без акциза{else}-{/if}</td>
                                <td style="text-align:center;">{if $row.tax_rate == 0}без НДС{else}{if $is_four_order eq true}{$row.tax_rate}%/1{$row.tax_rate}%{else}{$row.tax_rate}%{/if}{/if}</td>
                                <td style="text-align:center;">
                                    {if $row.sum_tax == 0}
                                        -
                                    {else}
                                        {$row.sum_tax|round:2}
                                    {/if}
                                </td>
                                <td style="text-align:center;">
                                    {$row.sum|round:2}
                                </td>
                                {if $inv_is_new3}
                                    <td style="text-align:center;">{if !isset($row.country_id) || $row.country_id == 0}-{else}{$row.country_id}{/if}</td>{/if}
                                <td style="text-align:center;">{if isset($row.country_name)}{$row.country_name|default:"-"}{else}-{/if}</td>
                                <td style="text-align:center;">{if isset($row.gtd)}{$row.gtd|default:"-"}{else}-{/if}</td>
                                {if $inv_is_new7}
                                    <td style="text-align:center;">-</td>
                                    <td style="text-align:center;">-</td>
                                    <td style="text-align:center;">-</td>
                                {/if}
                            </tr>
                        {/foreach}
                        <tr>
                            {if $inv_is_new4}
                                <td {if $inv_is_new7}style=""
                                    {/if}colspan={if $inv_is_new7}7{else}{if $isChanges20171001}6{else}{if $inv_is_new3}5{else}4{/if}{/if}{/if}>
                                    <b>Всего к оплате{if $inv_is_new7} (9){/if}<b></td>
                                <td style="text-align:center;">{if $is_four_order}-{else}{$bill.sum_without_tax|round:2}{/if}</td>
                                <td style="text-align:center;">-</td>
                                <td style="text-align:center;">-</td>
                            {else}
                                <td colspan={if $inv_is_new3}8{else}7{/if}><b>Всего к оплате<b></td>
                            {/if}
                            <td style="text-align:center;">
                                {if $bill.sum_tax == 0 && $bill.sum}
                                    -
                                {else}
                                    {$bill.sum_tax|round:2}
                                {/if}
                            </td>
                            <td style="text-align:center;">{$bill.sum|round:2}</td>
                            <td colspan={if $inv_is_new3}3{else}2{/if}>&nbsp;</td>
                            {if $inv_is_new7}
                                <td></td>
                                <td></td>
                                <td></td>
                            {/if}
                        </tr>

                    </table>
                </center>
            </div>
            <br>
            {if $inv_is_new3 && !$inv_is_new7}
                Итого: {$bill.sum|wordify:'RUB'}
            {/if}
            {if $inv_is_new7}<br />{/if}
            <div style="text-align:center;">
                <table border="0" cellpadding="0" cellspacing="5" align="left">
                    <tr>
                        <td><p style="text-align:right;">Руководитель&nbsp;организации{if $inv_is_new4}
                            <br>или иное уполномоченное лицо{/if}:</td>
                        <td>
                            {if (isset($emailed) && $emailed == 1) || $invoice_source eq 5}

                                {if $firm_director.sign}
                                    <img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_director.sign.src}"
                                         border="0" alt=""
                                         align="top"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}>
                                {else} _________________________________ {/if}


                            {else}
                                <br>
                                ________________________________
                                <br>
                                <br>
                            {/if}</td>
                        <td nowrap>/ {$firm_director.name} /</td>

                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><p style="text-align:right;">&nbsp;Главный&nbsp;бухгалтер{if $inv_is_new4}
                            <br>или иное уполномоченное лицо{/if}:</td>
                        <td>{if (isset($emailed) && $emailed == 1) || $invoice_source eq 5}

                                {if $firm_buh.sign}<img
                                    src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_buh.sign.src}"
                                    border="0" alt=""
                                    align="top"{if $firm_buh.sign.width} width="{$firm_buh.sign.width}" height="{$firm_buh.sign.height}"{/if}>{else} _________________________________ {/if}

                            {else}
                                <br>
                                ________________________________
                                <br>
                                <br>
                            {/if}</td>
                        <td nowrap>
                            / {$firm_buh.name} /
                        </td>
                    </tr>


                    <tr>
                        <td></td>
                        <td style="text-align:center;"><small>(подпись)</small></td>
                        <td style="text-align:center;"><small>(ф.и.о.)</small></td>
                        <td></td>
                        <td></td>
                        <td style="text-align:center;"><small>(подпись)</small></td>
                        <td style="text-align:center;"><small>(ф.и.о.)</small></td>
                    </tr>

                    {if $inv_is_new5}
                        {if !$isChanges20170801}
                            <tr>
                                <td><p style="text-align:right;">За генерального директора:</p></td>
                                <td>
                                    <br>________________________________<br><br></td>
                                <td></td>

                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td><p style="text-align:right;">За главного&nbsp;бухгалтер:</td>
                                <td>

                                    <br>________________________________<br><br></td>
                                <td>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="text-align:center;"><small>(подпись)</small></td>
                                <td style="text-align:center;"></td>
                                <td></td>
                                <td></td>
                                <td style="text-align:center;"><small>(подпись)</small></td>
                                <td style="text-align:center;"></td>
                            </tr>
                        {else}
                            <tr>
                                <td colspan="7">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="7">&nbsp;</td>
                            </tr>
                        {/if}

                    {/if}{*if $inv_is_new5*}
                    <tr>
                        <td colspan=4><p><br>Индивидуальный предприниматель{if $isChanges20171001}
                            <br>или иное уполномоченное лицо{/if} ___________________&nbsp;&nbsp;&nbsp;&nbsp;
                                _______________________</p>
                        </td>
                        <td colspan=3><p><br>&nbsp; &nbsp;____________________________________________________________________
                            </p></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td align=center><small>(подпись)</small></td>
                        <td align=center><small>(ф.и.о.)</small></td>
                        <td></td>
                        <td align=center colspan=3><small>(реквизиты свидетельства о государственной регистрации
                                индивидуального предпринимателя)</small></td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
</div>
{if !$isChanges20170801}<small>Примечание: Первый экземпляр - покупателю, второй экземпляр - продавцу.</small>{/if}

{if $stamp == "solop_nm"}
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_nm.png"
         style="position:relative;left:-30px;top:-138px;z-index:-10; margin-bottom:-90px;" width=68 height=40>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_nm.png"
         style="position:relative;left: 300px;top:-138px;z-index:-10; margin-bottom:-290px;" width=68 height=40>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_nm.png"
         style="position:relative;left:-340px;top:-101px;z-index:-10; margin-bottom:-90px;" width=164 height=79>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_nm.png"
         style="position:relative;left:10px;top:-101px;z-index:-10; margin-bottom:-90px;" width=164 height=79>
{/if}
{if $stamp == "solop_tp"}
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
         style="position:relative;left:-30px;top:-125px;z-index:-10; margin-bottom:-90px;" width=30 height=26>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
         style="position:relative;left: 350px;top:-125px;z-index:-10; margin-bottom:-290px;" width=30 height=26>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png"
         style="position:relative;left:-40px;top:-101px;z-index:-10; margin-bottom:-90px;" width=164 height=56>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png"
         style="position:relative;left:210px;top:-81px;z-index:-10; margin-bottom:-90px;" width=164 height=56>
    {if false}
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
             style="position:relative;left:-30px;top:-202px;z-index:-10; margin-bottom:-90px;" width=30 height=26>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
             style="position:relative;left: 350px;top:-402px;z-index:-10; margin-bottom:-290px;" width=30 height=26>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png"
             style="position:relative;left:-340px;top:-101px;z-index:-10; margin-bottom:-90px;" width=164 height=56>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png"
             style="position:relative;left:10px;top:-101px;z-index:-10; margin-bottom:-90px;" width=164 height=56>
    {/if}
{/if}
{if $stamp == "uskova"}
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png"
         style="position:relative;left:-30px;top:-130px;z-index:-10; margin-bottom:-90px;" width=37 height=49>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png"
         style="position:relative;left: 350px;top:-135px;z-index:-10; margin-bottom:-290px;" width=37 height=49>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png"
         style="position:relative;left:-50px;top:-100px;z-index:-10; margin-bottom:-90px;" width=142 height=47>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png"
         style="position:relative;left:220px;top:-101px;z-index:-10; margin-bottom:-90px;" width=142 height=47>
    {*FF*}
    {if false}
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png"
             style="position:relative;left:-30px;top:-180px;z-index:-10; margin-bottom:-90px;" width=37 height=49>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png"
             style="position:relative;left: 350px;top:-380px;z-index:-10; margin-bottom:-290px;" width=37 height=49>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png"
             style="position:relative;left:-50px;top:-150px;z-index:-10; margin-bottom:-90px;" width=142 height=47>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png"
             style="position:relative;left:220px;top:-131px;z-index:-10; margin-bottom:-90px;" width=142 height=47>
    {/if}
{/if}
{if $stamp == "zam_solop_tp"}
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
         style="position:relative;left:-30px;top:-125px;z-index:-10; margin-bottom:-90px;" width=30 height=26>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
         style="position:relative;left: 350px;top:-125px;z-index:-10; margin-bottom:-290px;" width=30 height=26>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png"
         style="position:relative;left:-50px;top:-121px;z-index:-10; margin-bottom:-90px;" width=174 height=68>
    <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png"
         style="position:relative;left:160px;top:-101px;z-index:-10; margin-bottom:-90px;" width=174 height=68>
    {*FF*}
    {if false}
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
             style="position:relative;left:-30px;top:-202px;z-index:-10; margin-bottom:-90px;" width=30 height=26>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png"
             style="position:relative;left: 350px;top:-402px;z-index:-10; margin-bottom:-290px;" width=30 height=26>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png"
             style="position:relative;left:-50px;top:-121px;z-index:-10; margin-bottom:-90px;" width=174 height=68>
        <img alt="Signature" src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png"
             style="position:relative;left:160px;top:-101px;z-index:-10; margin-bottom:-90px;" width=174 height=68>
    {/if}
{/if}
</body>
</html>
