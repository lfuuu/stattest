<html>

<head>
<LINK title=default href="{if $is_pdf == '1'}{$WEB_PATH}{else}{$PATH_TO_ROOT}{/if}invoice.css" type=text/css rel=stylesheet>
<title>СЧЕТ-ФАКТУРА N {$bill.bill_no}{$inv_no} от {$inv_date|mdate:"d.m.Y г."}</title>
<META http-equiv=Content-Type content="text/html; charset=koi8-r">
<style>
@page {literal}{size: landscape;}
@page rotated {size: landscape;}{/literal}
{if $inv_is_new5}
{literal}
.ht {font-size: 9pt;}
.ht strong{font-size: 9pt;}
{/literal}
{/if}
</style>
</head>

<body bgcolor="#FFFFFF" text="#000000">

{if $negative_balance}<h2 style="color:red">Внимание! Не достаточно средств для проведения авансовых платежей!</h2>{/if}
<div align="center"><center>
<table border="0" cellpadding="0" cellspacing="15">
{if $inv_is_new3}
<tr><td colspan="2"><p align="center">
<strong>СЧЕТ-ФАКТУРА N&nbsp;{if $is_four_order eq true}AB-{/if}{$bill.bill_no}{$inv_no}
        {if !$without_date_date} 
            от {if $is_four_order && isset($inv_pays)}
                    {$inv_pays[0].payment_date_ts|mdate:"d.m.Y г."}
               {else}
                    {$inv_date|mdate:"d.m.Y г."}
                {/if}
        {else} 
            {$without_date_date|mdate:"от d.m.Y г."}
        {/if}<br>
ИСПРАВЛЕНИЕ N ----- от -----</strong></p></tr>
{/if}
  <tr>
    {if $bill_client.firma=='all4geo'}
    <td valign="top" width="55%" class="ht">Продавец: <strong>
          Общество с ограниченной ответственностью "Олфогео"
            </strong><br>
      Адрес: <strong>115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8</strong><br>
      ИНН/КПП продавца: <strong>7727752091 / 772401001</strong><br>
      Грузоотправитель и его адрес:
      <strong>ООО "Олфогео"</strong><br>
        <strong>115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8</strong><br>


{elseif $bill_client.firma=='ooomcn'}
	<td valign="top" width="55%" class="ht">Продавец: <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "МСН" {if $bill.bill_date < '2012-01-24'}(ООО "МСН"){/if}{else}ООО "МСН"{/if}</strong><br>
    Адрес: <strong>117574 г. Москва, Одоевского пр-д., д. 3, кор. 7</strong><br>
    ИНН/КПП продавца: <strong>7728638151 / 772801001</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "МСН"</strong><br>
    <strong>117574 г. Москва, Одоевского пр-д., д. 3, кор. 7</strong><br>{/if}
{elseif $bill_client.firma=='ooocmc'}
	<td valign="top" width="55%" class="ht">Продавец: <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "Си Эм Си"{if $bill.bill_date < '2012-01-24'} (ООО "Си Эм Си"){/if}{else}ООО "Си Эм Си"{/if}</strong><br>
    Адрес: <strong>117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97</strong><br>
    ИНН/КПП продавца: <strong>7727701308 / 772701001</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "Си Эм Си"</strong><br>
    <strong>117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97</strong><br>{/if}
{elseif $bill_client.firma=='all4net'}
	<td valign="top" width="55%" class="ht">Продавец: {if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "Олфонет"{if $bill.bill_date < '2012-01-24'} (ООО "Олфонет"){/if}{else}ООО "Олфонет"{/if}</strong><br>
    
    
{if $bill.ts >= strtotime("2013-08-13")}
    Адрес: <strong>117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130</strong><br>
{else}
    Адрес: <strong>117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97</strong><br>
{/if}
    ИНН/КПП продавца: <strong>7727731060 / 772701001</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "Олфонет"</strong><br>

    {if $bill.ts >= strtotime("2013-08-13")}
        Адрес: <strong>117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130</strong><br>
    {else}
        Адрес: <strong>117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97</strong><br>
    {/if}
{/if}

{elseif $bill_client.firma == "markomnet"}
    <td valign="top" width="55%" class="ht">Продавец: <strong>ООО "МАРКОМНЕТ"</strong><br>
    Адрес: <strong>123458, г. Москва, Таллинская ул., д.2, кв. 282</strong><br>
    Телефон: <strong>(095) 950-5678</strong><br>
    ИНН/КПП продавца: <strong>7734246040&nbsp;/&nbsp;773401001</strong><br>
    Грузоотправитель и его адрес: {if ('2009-06-01' <= $bill.bill_date && $invoice_source <> 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "МАРКОМНЕТ"</strong><br>
    <strong>123458, г. Москва, Таллинская ул., д.2/282</strong><br>{/if}
{elseif $bill_client.firma == "mcn_telekom"}

    <td valign="top" width="55%" class="ht">Продавец: <strong>{$firm.name}</strong><br>
    Адрес: <strong>{$firm.address}</strong><br>
    Телефон: <strong>{$firm.phone}</strong><br>
    ИНН/КПП продавца: <strong>{$firm.inn}&nbsp;/&nbsp;{$firm.kpp}</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="41" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "МСН Телеком"</strong><br>
    <strong>{$firm.address}</strong><br>{/if}

{if false}
    <td valign="top" width="55%" class="ht">Продавец: <strong>ООО "МСН Телеком"</strong><br>
    Адрес: <strong>115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8</strong><br>
    Телефон: <strong>(495) 950-56-78</strong><br>
    ИНН/КПП продавца: <strong>7727752084&nbsp;/&nbsp;772401001</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="41" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "МСН Телеком"</strong><br>
    <strong>115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8</strong><br>{/if}
    {/if}

{elseif $bill_client.firma == "markomnet_service"}
    <td valign="top" width="55%" class="ht">Продавец: <strong>ООО "Маркомнет сервис"</strong><br>
    Адрес: <strong>117574, Москва, Одоевского проезд, д.3, к.7</strong><br>
    Телефон: <strong>(495) 638-63-84</strong><br>
    ИНН/КПП продавца: <strong>7728802130&nbsp;/&nbsp;772801001</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="41" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "Маркомнет сервис"</strong><br>
    <strong>117574, Москва, Одоевского проезд, д.3, к.7</strong><br>{/if}
{elseif $bill_client.firma == "mcm"}
    <td valign="top" width="55%" class="ht">Продавец: <strong>ООО "МСМ"</strong><br>
    Адрес: <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong><br>
    Телефон: <strong>(495) 950-58-41</strong><br>
    ИНН/КПП продавца: <strong>7727667833&nbsp;/&nbsp;772701001</strong><br>
    Грузоотправитель и его адрес: <strong>ООО "МСМ"</strong><br>
    <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong><br>
{elseif $bill_client.firma == "markomnet_new"}
    <td valign="top" width="55%" class="ht">Продавец: <strong>ООО "МАРКОМНЕТ"</strong><br>
    Адрес: <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong><br>
    Телефон: <strong>638-638-4</strong><br>
    ИНН/КПП продавца: <strong>7727702076&nbsp;/&nbsp;772701001</strong><br>
    Грузоотправитель и его адрес: <strong>ООО "МАРКОМНЕТ"</strong><br>
    <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong><br>
{elseif $bill_client.firma=='mcn'}{* || ($bill_client.nal=='beznal' && $bill.ts>=strtotime('2006-07-01') && $bill.comment!="разбивка Markomnet")*}
  <td valign="top" width="55%" class="ht">Продавец: <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "Эм Си Эн"{if $bill.bill_date < '2012-01-24'} (ООО "Эм Си Эн"){/if}{else}ООО "Эм Си Эн"{/if}</strong><br>
    Адрес: <strong>113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130</strong><br>
    ИНН/КПП продавца: <strong>7727508671 / 772701001</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------<br />{else}<strong>ООО "Эм Си Эн"</strong><br>
    <strong>113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130</strong><br>{/if}
{else}
  <td valign="top" width="55%" class="ht">Продавец: <strong>{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}{$firm.name_full}{if $bill.bill_date < '2012-01-24'} ({$firm.name}){/if}{else}{$firm.name}{/if}</strong><br>
    Адрес: <strong>{$firm.address}</strong><br>
    ИНН/КПП продавца: <strong>{$firm.inn} / {$firm.kpp}</strong><br>
    Грузоотправитель и его адрес: {if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="40" name="mysec"}&nbsp;{/section}------<br />{else}<strong>{$firm.name}</strong><br>
    <strong>{$firm.address}</strong><br>{/if}
{/if}
    Грузополучатель и его адрес: {if $bill_client.is_with_consignee && $bill_client.consignee}<strong>{$bill_client.consignee}</strong><br>{else}{if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}{section loop="41" name="mysec"}&nbsp;{/section}------<br />{else}<strong>{$bill_client.company_full}</strong><br>
    <strong>{$bill_client.address_post}</strong><br>{/if}{/if}
К платежно-расчетному документу{if isset($inv_pays)} {foreach from=$inv_pays item=inv_pay name=outer}N{$inv_pay.payment_no} от {$inv_pay.payment_date_ts|mdate:"d.m.Y г."}{if !$smarty.foreach.outer.last}, {/if}{/foreach}{/if}<br>
    Покупатель: <strong>{if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}</strong><br>
    Адрес: <strong>{if $bill_client.head_company_address_jur}{$bill_client.head_company_address_jur}{else}{$bill_client.address_jur}{/if}</strong><br>
    ИНН/КПП покупателя: <strong>{$bill_client.inn}&nbsp;/{$bill_client.kpp}</strong><br>
    Дополнение: <strong>к счету N: {$bill.bill_no}</strong><br>
    {if $inv_is_new3}Валюта: наименование Российский рубль, код 643{/if}</td>

    <td align=right valign="top" width="45%">
    <small>    Приложение N1<br>
    {if $inv_is_new3}к постановлению Правительства<br>Российской Федерации<br>
		от 26 декабря 2011 г. N 1137{else}к Правилам ведения журналов учета полученных и выставленных счетов-фактур,<br>
    книг покупок и книг продаж при расчетах но налогу на добавленную стоимость,<br>
    утвержденным постановлением правительства Российской Федерации от 2 декабря 2000 г. N 914<br>
    (в редакции постановлений Правительства Российской Федерации<br>
    от 10 марта 2001 г. N 189, от 27 июля 2002 г. N 575, от 16 февраля 2004 г. N 84{if $inv_is_new}, от 11 мая 2006 г. N 283{/if}{if $inv_is_new2}, от 26 мая 2009 г. N 451{/if}{if $inv_is_new3}, от 26 декабря 2011 г. N 1137{/if}){/if}
    </small></td>

  </tr>
  <tr>

    <td colspan="2">
    {if !$inv_is_new3}<p align="center"><strong>СЧЕТ-ФАКТУРА N&nbsp;{if $is_four_order eq true}AB-{/if}{$bill.bill_no}{$inv_no}
        {if !$without_date_date}
            от  {if $is_four_order && isset($inv_pays)}
                    {$inv_pays[0].payment_date_ts|mdate:"d.m.Y г."}
                {else}
                    {$inv_date|mdate:"d.m.Y г."}
                {/if}
        {else} 
            {$without_date_date|mdate:"от d.m.Y г."}
        {/if}</strong></p>{/if}
{if !$bill.tax}Для официальных нужд международной организации.<br>{/if}
{if !$inv_is_new3}Валюта: руб.{/if}
{*if !$inv_is_new3}Наименование и код валюты: руб. (643){/if*}
    <div align="center"><center><table border="1" cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <th{if $inv_is_new3} rowspan=2{/if}>Наименование<br>товара<br>(описание выполненных работ, оказанных услуг){if $inv_is_new},<br>имущественного права{/if}</th>
        {if !$inv_is_new3}<th>Еди-<br>ница<br>изме-<br>рения</th>{else}<th colspan=2>Единица<br>измерения</th>{/if}
        <th{if $inv_is_new3} rowspan=2{/if}>
            Коли-<br>чество{if $inv_is_new6}<br>(объем){/if}
        </th>
        <th{if $inv_is_new3} rowspan=2{/if}>
            {if $inv_is_new6}
            Цена<br>(тариф)<br>за еди-<br>ницу изме-<br>рения
            {else}
            Цена<br>(та-<br>риф)<br>за еди-<br>ницу изме-<br>рения{if $inv_is_new3}<br>руб{/if}
            {/if}
        </th>
        <th{if $inv_is_new3} rowspan=2{/if}>
            {if $inv_is_new6}
                Стоимость<br>товаров (работ,<br>услуг){if $inv_is_new},<br>имущественных<br>прав{/if} без налога-<br>всего
            {else}
                Стои-<br>мость<br>товаров<br>(работ,<br>услуг){if $inv_is_new},<br>имущественных<br>прав{/if}, всего без<br>налога{if $inv_is_new3}<br>руб{/if}
            {/if}
        </th>
        <th{if $inv_is_new3} rowspan=2{/if}>
            в том<br>чис-<br>ле<br>{if $inv_is_new6}сумма<br>{/if}акциз{if $inv_is_new6}а{/if}
        </th>
        <th{if $inv_is_new3} rowspan=2{/if}>
            Нало-<br>говая<br>ставка
        </th>
        <th{if $inv_is_new3} rowspan=2{/if}>
            Сумма{if $inv_is_new6} налога,<br> предъявляемая<br>покупателю{else}<br>нало-<br>га,{if $inv_is_new3}<br>руб{/if}{/if}
        </th>
        <th{if $inv_is_new3} rowspan=2{/if}>
            {if $inv_is_new6}
                Стоимость товаров<br>(работ, услуг),<br>имущественных <br>прав с налогом-<br>всего
            {else}
                Стоимость<br>товаров<br>(работ,<br>услуг)
                    {if $inv_is_new},<br>имущественных прав{/if}
                ,<br>всего с<br>учетом<br>налога
                    {if $inv_is_new3}<br>руб
            {/if}

        {/if}</th>
        {if !$inv_is_new3}<th>Стра-<br>на проис-<br> хожде-<br> ния</th>{else}<th colspan=2>Страна<br>происхождения<br>товара</th>{/if}
        <th{if $inv_is_new3} rowspan=2{/if}>Номер<br> тамо-<br> женной<br> декла-<br> рации</th>
      </tr>
      {if $inv_is_new3}<tr>
      	<th>к<br>о<br>д</th>
      	<th>условное<br>обозначение<br>(национальное)</th>
      	<th>цифровой<br>код</th>
      	<th>краткое<br>наимено-<br>вание</th>
      </tr>{/if}
      <tr>
        <td align="center">1</td>
        <td align="center">2</td>
        {if $inv_is_new3}<td align="center">2а</td>{/if}
        <td align="center">3</td>
        <td align="center">4</td>
        <td align="center">5</td>
        <td align="center">6</td>
        <td align="center">7</td>
        <td align="center">8</td>
        <td align="center">9</td>
        <td align="center">10</td>
        {if $inv_is_new3}<td align="center">10а</td>{/if}
        <td align="center">11</td>
      </tr>
{foreach from=$bill_lines item=row key=key}
      <tr>
        <td>{$row.item}</td>
        {if $inv_is_new3}
        <td align="center">

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
                    {if $row.okvd_code}
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
        <td align="center">
            {if $is_four_order}
                -
            {else}
                {if $inv_is_new4}
                    {if $row.okvd_code}
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

      <td align="center">
{if $bill.bill_date < "2012-01-01"}{$row.amount|round:4}{else}
            {if $is_four_order}
                -
            {else}
                {if $inv_is_new4}
                    {if $row.okvd_code}
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
      <td align="center">


            {if $is_four_order}
                -
            {else}
                {if $inv_is_new4}
                    {if $row.okvd_code}
                        {$row.outprice|round:2}
                    {else}
                        {if $row.type == "service"}
                            -
                        {else}
                            {$row.outprice|round:2}
                        {/if}
                    {/if}
                {else}
                    {$row.outprice|round:2}
                {/if}
            {/if}


{*if $is_four_order}-{else}{if $row.okvd_code || $row.type != "service"}{$row.outprice|round:2}{else}-{/if}{/if*}
</td>
        <td align="center">
            {if $is_four_order}
                -
            {else}
                {if $bill_client.nds_calc_method != 1}
                    {$row.sum|mround:2:2}
                {else}
                    {$row.sum|mround:4:4}
                {/if}
                    
            {/if}</td>
        <td align="center" nowrap>{if $inv_is_new4}без акциза{else}-{/if}</td>
        <td align="center">{if $row.tax == 0}без НДС{else}{if $is_four_order eq true}18%/118%{else}18%{/if}{/if}</td>
        <!--td align="center">{if $row.tax == 0 && $bill.tax == 0 && $bill.sum}-{else}{$row.tax|round:4}{/if}</td-->
        <td align="center">
            {if $bill_client.nds_calc_method != 1}
                {$row.tax|string_format:"%.2f"}
            {else}
                {if $row.tax == 0 && $row.line_nds == 0} {*&& $bill.tax == 0 && $bill.sum*}
                    -
                {else}
                    {$row.tsum/1.18*0.18|round:4}
                {/if}
            {/if}</td>
        <td align="center">
            {if $bill_client.nds_calc_method != 1}
                {$row.tsum|round:2}
            {else}
                {$row.tsum|round:4}
            {/if}
            </td>
        {if $inv_is_new3}<td align="center">{if $row.country_id == 0}-{else}{$row.country_id}{/if}</td>{/if}
        <td align="center">{$row.country_name|default:"-"}</td>
        <td align="center">{$row.gtd|default:"-"}</td>
      </tr>
{/foreach}
     <tr>
     	{if $inv_is_new4}
     	<td colspan={if $inv_is_new3}5{else}4{/if}><b>Всего к оплате<b></td>
     	<td align="center">{if $is_four_order}-{else}{$bill.sum|round:2}{*$bill.tsum/1.18|round:2*}{/if}</td>
     	<td>&nbsp;</td>
     	<td>&nbsp;</td>
     	{else}
        <td colspan={if $inv_is_new3}8{else}7{/if}><b>Всего к оплате<b></td>
        {/if}
        <td align="center">
            {if $bill_client.nds_calc_method != 1}
                {$bill.tax|string_format:"%.2f"}
            {else}
                {if $bill.tax == 0 && $bill.sum}
                    -
                {else}
                    {$bill.tsum/1.18*0.18|round:2}
                {/if}
            {/if}</td>
        <td align="center">{$bill.tsum|round:2}</td>
        <td colspan={if $inv_is_new3}3{else}2{/if}>&nbsp;</td>
      </tr>

    </table>
    </center></div>
<br>
{if $inv_is_new3}
Итого: {$bill.tsum|wordify:'RUR'}
{/if}
    <div align="center">
    <table border="0" cellpadding="0" cellspacing="5" align="left">
      <tr >
        <td><p align="right">Руководитель&nbsp;организации{if $inv_is_new4}<br>или иное уполномоченное лицо{/if}:</td>
        <td>
        {if isset($emailed) || $invoice_source eq 5}

            {if $firm_director.sign} <img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_director.sign.src}"  border="0" alt="" align="top"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}> {else} _________________________________ {/if}


        {else}<br>________________________________<br><br>{/if}</td>
    <td nowrap>/ {$firm_director.name} /</td>

    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td><p align="right">&nbsp;Главный&nbsp;бухгалтер{if $inv_is_new4}<br>или иное уполномоченное лицо{/if}:</td>
    <td>{if isset($emailed) || $invoice_source eq 5}

            {if $firm_buh.sign}<img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_buh.sign.src}"  border="0" alt="" align="top"{if $firm_buh.sign.width} width="{$firm_buh.sign.width}" height="{$firm_buh.sign.height}"{/if}>{else} _________________________________ {/if}

{else}<br>________________________________<br><br>{/if}</td>
        <td nowrap>
            / {$firm_buh.name} /
		</td>
      </tr>


      <tr>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
        <td align="center"><small>(ф.и.о.)</small></td>
    <td></td>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
        <td align="center"><small>(ф.и.о.)</small></td>
      </tr>

    {if $inv_is_new5}
      <tr >
        <td><p align="right">За генерального директора:</td>
        <td>
        <br>________________________________<br><br></td>
    <td></td>

    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td><p align="right">За главного&nbsp;бухгалтер:</td>
    <td>

<br>________________________________<br><br></td>
        <td>
		</td>
      </tr>


      <tr>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
        <td align="center"></td>
    <td></td>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
        <td align="center"></td>
      </tr>

{/if}{*if $inv_is_new5*}
      <tr>
        <td colspan=4><p><br>Индивидуальный предприниматель ___________________&nbsp;&nbsp;&nbsp;&nbsp; _______________________</p>
    </td>
	<td colspan=3><p><br>&nbsp; &nbsp;____________________________________________________________________</p></td>
      </tr>

	<tr>
<td></td><td align=center><small>(подпись)</small></td>
<td align=center><small>(ф.и.о.)</small></td>
<td></td>
<td align=center colspan=3><small>(реквизиты свидетельства о государственной регистрации индивидуального предпринимателя)</small></td>
</tr>
    </table>
    </div>
   </td>
  </tr>
</table>
</center></div>
<small>Примечание: Первый экземпляр - покупателю, второй экземпляр - продавцу.</small>

{if $stamp == "solop_nm"}
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_nm.png" 
style="position:relative;left:-30;top:-138;z-index:-10; margin-bottom:-90px;" width=68 height=40>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_nm.png" 
style="position:relative;left: 300;top:-138;z-index:-10; margin-bottom:-290px;" width=68 height=40>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_nm.png" 
style="position:relative;left:-340;top:-101;z-index:-10; margin-bottom:-90px;" width=164 height=79>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_nm.png" 
style="position:relative;left:10;top:-101;z-index:-10; margin-bottom:-90px;" width=164 height=79>
{/if}
{if $stamp == "solop_tp"}
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left:-30;top:-125;z-index:-10; margin-bottom:-90px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left: 350;top:-125;z-index:-10; margin-bottom:-290px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png" 
style="position:relative;left:-40;top:-101;z-index:-10; margin-bottom:-90px;" width=164 height=56>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png" 
style="position:relative;left:210;top:-81;z-index:-10; margin-bottom:-90px;" width=164 height=56>
{if false}
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left:-30;top:-202;z-index:-10; margin-bottom:-90px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left: 350;top:-402;z-index:-10; margin-bottom:-290px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png" 
style="position:relative;left:-340;top:-101;z-index:-10; margin-bottom:-90px;" width=164 height=56>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_solop_tp.png" 
style="position:relative;left:10;top:-101;z-index:-10; margin-bottom:-90px;" width=164 height=56>
{/if}
{/if}
{if $stamp == "uskova"}
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png" 
style="position:relative;left:-30;top:-130;z-index:-10; margin-bottom:-90px;" width=37 height=49>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png" 
style="position:relative;left: 350;top:-135;z-index:-10; margin-bottom:-290px;" width=37 height=49>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png" 
style="position:relative;left:-50;top:-100;z-index:-10; margin-bottom:-90px;" width=142 height=47>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png" 
style="position:relative;left:220;top:-101;z-index:-10; margin-bottom:-90px;" width=142 height=47>
{*FF*}
{if false}
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png" 
style="position:relative;left:-30;top:-180;z-index:-10; margin-bottom:-90px;" width=37 height=49>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_uskova.png" 
style="position:relative;left: 350;top:-380;z-index:-10; margin-bottom:-290px;" width=37 height=49>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png" 
style="position:relative;left:-50;top:-150;z-index:-10; margin-bottom:-90px;" width=142 height=47>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_uskova.png" 
style="position:relative;left:220;top:-131;z-index:-10; margin-bottom:-90px;" width=142 height=47>
{/if}
{/if}
{if $stamp == "zam_solop_tp"}
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left:-30;top:-125;z-index:-10; margin-bottom:-90px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left: 350;top:-125;z-index:-10; margin-bottom:-290px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png" 
style="position:relative;left:-50;top:-121;z-index:-10; margin-bottom:-90px;" width=174 height=68>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png" 
style="position:relative;left:160;top:-101;z-index:-10; margin-bottom:-90px;" width=174 height=68>
{*FF*}
{if false}
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left:-30;top:-202;z-index:-10; margin-bottom:-90px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/sign_solop_tp.png" 
style="position:relative;left: 350;top:-402;z-index:-10; margin-bottom:-290px;" width=30 height=26>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png" 
style="position:relative;left:-50;top:-121;z-index:-10; margin-bottom:-90px;" width=174 height=68>
<img src="{if $is_pdf == '1'}{$WEB_PATH}{/if}images/stamp_zam_solop_tp.png" 
style="position:relative;left:160;top:-101;z-index:-10; margin-bottom:-90px;" width=174 height=68>
{/if}
{/if}
</body>
</html>
